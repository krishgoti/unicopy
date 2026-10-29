<?php
/**
 * UniHelp - Mentorship System Controller (Full Feature)
 * Handles: mentor listing, profile, requests, respond, availability,
 *          slot management, session booking, and notifications.
 */

class MentorshipController extends Controller {

    // ─────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────

    private function ensureTablesExist(): void {
        try {
            $this->db->query("SELECT 1 FROM mentor_profiles LIMIT 1");
        } catch (Exception $e) {
            $queries = [
                "CREATE TABLE IF NOT EXISTS `mentor_profiles` (
                      `id`           INT AUTO_INCREMENT PRIMARY KEY,
                      `user_id`      INT NOT NULL UNIQUE,
                      `bio`          TEXT,
                      `skills`       TEXT,
                      `experience`   VARCHAR(120),
                      `linkedin`     VARCHAR(255),
                      `avatar_url`   VARCHAR(255),
                      `is_available` TINYINT(1) NOT NULL DEFAULT 1,
                      `max_slots`    TINYINT NOT NULL DEFAULT 4,
                      `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                
                "CREATE TABLE IF NOT EXISTS `mentor_slots` (
                      `id`          INT AUTO_INCREMENT PRIMARY KEY,
                      `mentor_id`   INT NOT NULL,
                      `slot_date`   DATE NOT NULL,
                      `slot_time`   TIME NOT NULL,
                      `duration`    SMALLINT NOT NULL DEFAULT 30,
                      `notes`       VARCHAR(255),
                      `is_booked`   TINYINT(1) NOT NULL DEFAULT 0,
                      `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                      FOREIGN KEY (`mentor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                
                "CREATE TABLE IF NOT EXISTS `slot_bookings` (
                      `id`          INT AUTO_INCREMENT PRIMARY KEY,
                      `slot_id`     INT NOT NULL,
                      `student_id`  INT NOT NULL,
                      `status`      ENUM('confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
                      `booked_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                      UNIQUE KEY `uq_slot` (`slot_id`),
                      FOREIGN KEY (`slot_id`) REFERENCES `mentor_slots`(`id`) ON DELETE CASCADE,
                      FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                
                "ALTER TABLE `mentorship_requests` ADD COLUMN IF NOT EXISTS `mentor_reply` TEXT AFTER `status`" ,
                "ALTER TABLE `mentorship_requests` ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`"
            ];
            
            foreach ($queries as $q) {
                try {
                    $this->db->exec($q);
                } catch (Exception $ex) {
                    // Log error if needed: error_log("Migration error: " . $ex->getMessage());
                }
            }
        }
    }

    private function getMentorProfile(int $userId): ?array {
        $this->ensureTablesExist();
        $stmt = $this->db->prepare("SELECT * FROM mentor_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    private function notify(int $toUserId, string $title, string $message, string $link = ''): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO notifications (user_id, title, message, type, link)
                 VALUES (?, ?, ?, 'mentorship', ?)"
            );
            $stmt->execute([$toUserId, $title, $message, $link ?: BASE_URL . '/mentorship']);
        } catch (Exception $e) { /* silent */ }
    }

    // ─────────────────────────────────────────────
    //  MAIN INDEX — routes to student / mentor view
    // ─────────────────────────────────────────────

    public function index(): void {
        Auth::requireLogin();
        Auth::requireApproved();

        $role = Auth::role();

        if ($role === 'student') {
            $this->studentDashboard();
        } elseif (in_array($role, ['faculty', 'alumni'])) {
            $this->mentorDashboard();
        } else {
            $this->redirect(BASE_URL . '/dashboard');
        }
    }

    // ─────────────────────────────────────────────
    //  STUDENT DASHBOARD
    // ─────────────────────────────────────────────

    private function studentDashboard(): void {
        $this->ensureTablesExist();
        $userId = Auth::id();

        /* All mentors (faculty + alumni) with profile info */
        $stmt = $this->db->query(
            "SELECT u.id, u.role, u.email,
                    up.full_name, up.company, up.designation, up.department,
                    COALESCE(mp.bio,'')          AS bio,
                    COALESCE(mp.skills,'')       AS skills,
                    COALESCE(mp.experience,'')   AS experience,
                    COALESCE(mp.linkedin,'')     AS linkedin,
                    COALESCE(mp.avatar_url,'')   AS avatar_url,
                    COALESCE(mp.is_available,0)  AS is_available
             FROM users u
             JOIN user_profiles up ON u.id = up.user_id
             LEFT JOIN mentor_profiles mp ON u.id = mp.user_id
             WHERE u.role IN ('faculty','alumni')
               AND u.status IN ('active','approved')
             ORDER BY mp.is_available DESC, up.full_name ASC"
        );
        $allMentors = $stmt->fetchAll() ?: [];

        /* Student's own requests */
        $stmt = $this->db->prepare(
            "SELECT mr.*, up.full_name AS mentor_name, up.company, up.designation,
                    u.email AS mentor_email, up.phone,
                    COALESCE(mp.skills,'') AS mentor_skills,
                    COALESCE(mp.experience,'') AS mentor_experience,
                    COALESCE(mp.avatar_url,'') AS mentor_avatar
             FROM mentorship_requests mr
             JOIN user_profiles up ON mr.mentor_id = up.user_id
             JOIN users u ON mr.mentor_id = u.id
             LEFT JOIN mentor_profiles mp ON mr.mentor_id = mp.user_id
             WHERE mr.student_id = ?
             ORDER BY mr.created_at DESC"
        );
        $stmt->execute([$userId]);
        $myRequests = $stmt->fetchAll() ?: [];

        /* Detailed statuses for connective buttons (pending/accepted) */
        $mentorStatuses = [];
        foreach ($myRequests as $r) {
            if ($r['status'] !== 'rejected') {
                $mentorStatuses[$r['mentor_id']] = $r['status'];
            }
        }

        /* Available slots for ALL mentors (future only) */
        $stmt = $this->db->query(
            "SELECT ms.*, up.full_name AS mentor_name,
                    sb.id AS booking_id, sb.student_id AS booked_by
             FROM mentor_slots ms
             JOIN user_profiles up ON ms.mentor_id = up.user_id
             LEFT JOIN slot_bookings sb ON ms.id = sb.slot_id
             WHERE ms.slot_date >= CURDATE() AND ms.is_booked = 0
             ORDER BY ms.slot_date ASC, ms.slot_time ASC"
        );
        $openSlots = $stmt->fetchAll() ?: [];

        /* Student's booked sessions */
        $stmt = $this->db->prepare(
            "SELECT sb.*, ms.slot_date, ms.slot_time, ms.duration, ms.notes,
                    up.full_name AS mentor_name, u.email AS mentor_email, up.phone
             FROM slot_bookings sb
             JOIN mentor_slots ms ON sb.slot_id = ms.id
             JOIN user_profiles up ON ms.mentor_id = up.user_id
             JOIN users u ON ms.mentor_id = u.id
             WHERE sb.student_id = ?
             ORDER BY ms.slot_date DESC"
        );
        $stmt->execute([$userId]);
        $myBookings = $stmt->fetchAll() ?: [];

        $this->view('mentorship/student', [
            'allMentors'     => $allMentors,
            'myRequests'     => $myRequests,
            'mentorStatuses' => $mentorStatuses,
            'openSlots'      => $openSlots,
            'myBookings'     => $myBookings,
        ]);
    }

    // ─────────────────────────────────────────────
    //  MENTOR DASHBOARD
    // ─────────────────────────────────────────────

    private function mentorDashboard(): void {
        $this->ensureTablesExist();
        $userId = Auth::id();

        /* Incoming requests */
        $stmt = $this->db->prepare(
            "SELECT mr.*, up.full_name AS student_name, up.department, up.phone,
                    u.email AS student_email
             FROM mentorship_requests mr
             JOIN user_profiles up ON mr.student_id = up.user_id
             JOIN users u ON mr.student_id = u.id
             WHERE mr.mentor_id = ?
             ORDER BY mr.created_at DESC"
        );
        $stmt->execute([$userId]);
        $requests = $stmt->fetchAll() ?: [];

        /* Mentor's own profile */
        $profile = $this->getMentorProfile($userId);

        /* Mentor's slots */
        $stmt = $this->db->prepare(
            "SELECT ms.*,
                    sb.id AS booking_id, sb.student_id,
                    up.full_name AS student_name, up.phone AS student_phone,
                    u.email AS student_email
             FROM mentor_slots ms
             LEFT JOIN slot_bookings sb ON ms.id = sb.slot_id
             LEFT JOIN users u ON sb.student_id = u.id
             LEFT JOIN user_profiles up ON sb.student_id = up.user_id
             WHERE ms.mentor_id = ?
             ORDER BY ms.slot_date DESC, ms.slot_time ASC"
        );
        $stmt->execute([$userId]);
        $slots = $stmt->fetchAll() ?: [];

        $this->view('mentorship/mentor', [
            'requests' => $requests,
            'profile'  => $profile,
            'slots'    => $slots,
        ]);
    }

    // ─────────────────────────────────────────────
    //  MENTOR VIEW PROFILE (public listing per mentor)
    // ─────────────────────────────────────────────

    public function profile($mentorId = null): void {
        Auth::requireLogin();
        Auth::requireApproved();
        $this->ensureTablesExist();

        $mentorId = (int)($mentorId ?? 0);
        if ($mentorId <= 0) {
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $stmt = $this->db->prepare(
            "SELECT u.id, u.role, u.email,
                    up.full_name, up.company, up.designation, up.department, up.phone,
                    COALESCE(mp.bio,'')          AS bio,
                    COALESCE(mp.skills,'')       AS skills,
                    COALESCE(mp.experience,'')   AS experience,
                    COALESCE(mp.linkedin,'')     AS linkedin,
                    COALESCE(mp.avatar_url,'')   AS avatar_url,
                    COALESCE(mp.is_available,0)  AS is_available
             FROM users u
             JOIN user_profiles up ON u.id = up.user_id
             LEFT JOIN mentor_profiles mp ON u.id = mp.user_id
             WHERE u.id = ? AND u.role IN ('faculty','alumni')"
        );
        $stmt->execute([$mentorId]);
        $mentor = $stmt->fetch();

        if (!$mentor) {
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        /* Mentor's available slots */
        $stmt = $this->db->prepare(
            "SELECT ms.* FROM mentor_slots ms
             LEFT JOIN slot_bookings sb ON ms.id = sb.slot_id
             WHERE ms.mentor_id = ? AND ms.slot_date >= CURDATE() AND ms.is_booked = 0
             ORDER BY ms.slot_date ASC, ms.slot_time ASC"
        );
        $stmt->execute([$mentorId]);
        $slots = $stmt->fetchAll() ?: [];

        /* Has student already requested? */
        $existingRequest = null;
        if (Auth::role() === 'student') {
            $stmt = $this->db->prepare(
                "SELECT * FROM mentorship_requests WHERE student_id = ? AND mentor_id = ? LIMIT 1"
            );
            $stmt->execute([Auth::id(), $mentorId]);
            $existingRequest = $stmt->fetch() ?: null;
        }

        $this->view('mentorship/profile', [
            'mentor'          => $mentor,
            'slots'           => $slots,
            'existingRequest' => $existingRequest,
        ]);
    }

    // ─────────────────────────────────────────────
    //  STUDENT: SEND REQUEST
    // ─────────────────────────────────────────────

    public function request(): void {
        Auth::requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $mentorId = (int)($_POST['mentor_id'] ?? 0);
        $message  = trim($_POST['message'] ?? '');

        if (!$mentorId) {
            $this->flash('error', 'Invalid mentor selected.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        /* Verify mentor exists */
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ? AND role IN ('faculty','alumni')");
        $stmt->execute([$mentorId]);
        if (!$stmt->fetch()) {
            $this->flash('error', 'Mentor not found.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        /* Duplicate pending check */
        $stmt = $this->db->prepare(
            "SELECT id FROM mentorship_requests WHERE student_id = ? AND mentor_id = ? AND status = 'pending'"
        );
        $stmt->execute([Auth::id(), $mentorId]);
        if ($stmt->fetch()) {
            $this->flash('error', 'You already have a pending request with this mentor.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO mentorship_requests (student_id, mentor_id, message) VALUES (?, ?, ?)"
        );
        $stmt->execute([Auth::id(), $mentorId, $message ?: null]);

        /* Get student name for notification */
        $stmt2 = $this->db->prepare("SELECT full_name FROM user_profiles WHERE user_id = ?");
        $stmt2->execute([Auth::id()]);
        $sName = $stmt2->fetchColumn() ?: 'A student';

        $this->notify($mentorId, 'New Mentorship Request',
            $sName . ' has requested you as a mentor.',
            BASE_URL . '/mentorship'
        );

        $this->flash('success', 'Mentorship request sent successfully!');
        $this->redirect(BASE_URL . '/mentorship');
    }

    // ─────────────────────────────────────────────
    //  MENTOR: RESPOND (accept / reject)
    // ─────────────────────────────────────────────

    public function respond(): void {
        Auth::requireMentor();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $requestId = (int)($_POST['request_id'] ?? 0);
        $action    = $_POST['action'] ?? '';
        $reply     = trim($_POST['reply'] ?? '');

        if (!in_array($action, ['accept', 'reject'])) {
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $stmt = $this->db->prepare(
            "SELECT * FROM mentorship_requests WHERE id = ? AND mentor_id = ? AND status = 'pending'"
        );
        $stmt->execute([$requestId, Auth::id()]);
        $req = $stmt->fetch();

        if (!$req) {
            $this->flash('error', 'Request not found or already responded.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $status = $action === 'accept' ? 'accepted' : 'rejected';
        $stmt = $this->db->prepare(
            "UPDATE mentorship_requests SET status = ?, mentor_reply = ?, updated_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$status, $reply ?: null, $requestId]);

        /* Get mentor name */
        $stmt2 = $this->db->prepare("SELECT full_name FROM user_profiles WHERE user_id = ?");
        $stmt2->execute([Auth::id()]);
        $mName = $stmt2->fetchColumn() ?: 'Your mentor';

        $msg = $action === 'accept'
            ? $mName . ' has accepted your mentorship request! 🎉'
            : $mName . ' has declined your mentorship request.';

        $this->notify($req['student_id'], 'Mentorship Request ' . ucfirst($status), $msg);

        $this->flash('success', 'Response sent successfully.');
        $this->redirect(BASE_URL . '/mentorship');
    }

    // ─────────────────────────────────────────────
    //  MENTOR: SAVE PROFILE
    // ─────────────────────────────────────────────

    public function saveProfile(): void {
        Auth::requireMentor();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $userId      = Auth::id();
        $bio         = trim($_POST['bio'] ?? '');
        $skills      = trim($_POST['skills'] ?? '');
        $experience  = trim($_POST['experience'] ?? '');
        $linkedin    = trim($_POST['linkedin'] ?? '');
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;
        $maxSlots    = max(1, min(20, (int)($_POST['max_slots'] ?? 4)));

        /* Handle avatar upload */
        $avatarUrl = '';
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                $dir = BASE_PATH . '/uploads/avatars/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $filename = 'mentor_' . $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $filename)) {
                    $avatarUrl = BASE_URL . '/uploads/avatars/' . $filename;
                }
            }
        }

        /* Upsert mentor_profiles */
        $existing = $this->getMentorProfile($userId);
        if ($existing) {
            $stmt = $this->db->prepare(
                "UPDATE mentor_profiles SET bio=?, skills=?, experience=?, linkedin=?,
                 is_available=?, max_slots=?" .
                ($avatarUrl ? ", avatar_url=?" : "") .
                " WHERE user_id=?"
            );
            $params = [$bio, $skills, $experience, $linkedin, $isAvailable, $maxSlots];
            if ($avatarUrl) $params[] = $avatarUrl;
            $params[] = $userId;
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO mentor_profiles (user_id, bio, skills, experience, linkedin, is_available, max_slots, avatar_url)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $params = [$userId, $bio, $skills, $experience, $linkedin, $isAvailable, $maxSlots, $avatarUrl];
        }
        $stmt->execute($params);

        $this->flash('success', 'Profile updated successfully.');
        $this->redirect(BASE_URL . '/mentorship');
    }

    // ─────────────────────────────────────────────
    //  MENTOR: TOGGLE AVAILABILITY
    // ─────────────────────────────────────────────

    public function toggleAvailability(): void {
        Auth::requireMentor();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $userId  = Auth::id();
        $newVal  = (int)($_POST['is_available'] ?? 0);
        $profile = $this->getMentorProfile($userId);

        if ($profile) {
            $stmt = $this->db->prepare("UPDATE mentor_profiles SET is_available=? WHERE user_id=?");
            $stmt->execute([$newVal, $userId]);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO mentor_profiles (user_id, is_available) VALUES (?, ?)"
            );
            $stmt->execute([$userId, $newVal]);
        }

        $this->flash('success', $newVal ? 'You are now marked as Available.' : 'You are now marked as Unavailable.');
        $this->redirect(BASE_URL . '/mentorship');
    }

    // ─────────────────────────────────────────────
    //  MENTOR: ADD SLOT
    // ─────────────────────────────────────────────

    public function addSlot(): void {
        Auth::requireMentor();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $userId   = Auth::id();
        $date     = trim($_POST['slot_date'] ?? '');
        $time     = trim($_POST['slot_time'] ?? '');
        $duration = max(15, min(120, (int)($_POST['duration'] ?? 30)));
        $notes    = trim($_POST['notes'] ?? '');

        if (!$date || !$time || strtotime($date) < strtotime('today')) {
            $this->flash('error', 'Please provide a valid future date and time.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO mentor_slots (mentor_id, slot_date, slot_time, duration, notes)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $date, $time, $duration, $notes ?: null]);

        $this->flash('success', 'Session slot added successfully!');
        $this->redirect(BASE_URL . '/mentorship');
    }

    // ─────────────────────────────────────────────
    //  MENTOR: DELETE SLOT
    // ─────────────────────────────────────────────

    public function deleteSlot(): void {
        Auth::requireMentor();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $slotId = (int)($_POST['slot_id'] ?? 0);
        $stmt   = $this->db->prepare(
            "DELETE FROM mentor_slots WHERE id = ? AND mentor_id = ? AND is_booked = 0"
        );
        $stmt->execute([$slotId, Auth::id()]);

        $this->flash('success', 'Slot deleted.');
        $this->redirect(BASE_URL . '/mentorship');
    }

    // ─────────────────────────────────────────────
    //  STUDENT: BOOK SLOT
    // ─────────────────────────────────────────────

    public function bookSlot(): void {
        Auth::requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $slotId    = (int)($_POST['slot_id'] ?? 0);
        $studentId = Auth::id();

        /* Verify slot exists and is free */
        $stmt = $this->db->prepare(
            "SELECT ms.*, up.full_name AS mentor_name, u.id AS mentor_user_id
             FROM mentor_slots ms
             JOIN user_profiles up ON ms.mentor_id = up.user_id
             JOIN users u ON ms.mentor_id = u.id
             WHERE ms.id = ? AND ms.is_booked = 0 AND ms.slot_date >= CURDATE()"
        );
        $stmt->execute([$slotId]);
        $slot = $stmt->fetch();

        if (!$slot) {
            $this->flash('error', 'This slot is no longer available.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        /* Already booked by this student? */
        $stmt = $this->db->prepare(
            "SELECT id FROM slot_bookings WHERE slot_id = ? AND student_id = ?"
        );
        $stmt->execute([$slotId, $studentId]);
        if ($stmt->fetch()) {
            $this->flash('error', 'You have already booked this slot.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "INSERT INTO slot_bookings (slot_id, student_id) VALUES (?, ?)"
            );
            $stmt->execute([$slotId, $studentId]);

            $stmt = $this->db->prepare("UPDATE mentor_slots SET is_booked = 1 WHERE id = ?");
            $stmt->execute([$slotId]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->flash('error', 'Booking failed. Please try again.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        /* Notifications */
        $stmt2 = $this->db->prepare("SELECT full_name FROM user_profiles WHERE user_id = ?");
        $stmt2->execute([$studentId]);
        $sName = $stmt2->fetchColumn() ?: 'A student';

        $this->notify(
            $slot['mentor_user_id'],
            'Session Booked',
            $sName . ' booked your slot on ' . date('d M Y', strtotime($slot['slot_date'])) . ' at ' . date('h:i A', strtotime($slot['slot_time'])) . '.',
        );

        $this->flash('success', 'Session booked successfully! Check your bookings below.');
        $this->redirect(BASE_URL . '/mentorship');
    }

    // ─────────────────────────────────────────────
    //  STUDENT: CANCEL BOOKING
    // ─────────────────────────────────────────────

    public function cancelBooking(): void {
        Auth::requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $studentId = Auth::id();

        $stmt = $this->db->prepare(
            "SELECT sb.*, ms.mentor_id, ms.slot_date, ms.slot_time
             FROM slot_bookings sb
             JOIN mentor_slots ms ON sb.slot_id = ms.id
             WHERE sb.id = ? AND sb.student_id = ?"
        );
        $stmt->execute([$bookingId, $studentId]);
        $booking = $stmt->fetch();

        if (!$booking) {
            $this->flash('error', 'Booking not found.');
            $this->redirect(BASE_URL . '/mentorship');
            return;
        }

        $this->db->beginTransaction();
        $stmt = $this->db->prepare("DELETE FROM slot_bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        $stmt = $this->db->prepare("UPDATE mentor_slots SET is_booked = 0 WHERE id = ?");
        $stmt->execute([$booking['slot_id']]);
        $this->db->commit();

        $this->notify(
            $booking['mentor_id'],
            'Session Cancelled',
            'A student cancelled their booking for ' . date('d M Y', strtotime($booking['slot_date'])) . '.'
        );

        $this->flash('success', 'Booking cancelled.');
        $this->redirect(BASE_URL . '/mentorship');
    }
}
