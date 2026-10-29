-- Seeding missing data for UniHelp
USE unihelp;

-- 1. Skills
INSERT INTO `skills` (`name`, `category`) VALUES
('Data Structures', 'technical'),
('Algorithms', 'technical'),
('Programming (C/C++)', 'coding'),
('Programming (Java)', 'coding'),
('Programming (Python)', 'coding'),
('Web Development', 'technical'),
('Database Management', 'technical'),
('Problem Solving', 'aptitude'),
('Logical Reasoning', 'aptitude'),
('Quantitative Aptitude', 'aptitude'),
('Verbal Ability', 'aptitude'),
('Communication', 'soft'),
('Teamwork', 'soft'),
('Leadership', 'soft'),
('Machine Learning', 'technical'),
('Cloud Computing', 'technical');

-- 2. Projects
INSERT INTO `projects` (`title`, `description`, `skills_required`, `difficulty`, `category`) VALUES
('E-Commerce Website', 'Build a full-stack e-commerce site with cart, checkout', '["Web Development","Database Management"]', 'intermediate', 'Web'),
('DSA Practice Tracker', 'Track your DSA progress with LeetCode integration', '["Data Structures","Algorithms","Programming (Python)"]', 'beginner', 'Tools'),
('Portfolio Website', 'Create a personal portfolio with projects showcase', '["Web Development","Programming (JavaScript)"]', 'beginner', 'Web'),
('Chat Application', 'Real-time chat with WebSockets', '["Web Development","Programming (Java)","Database Management"]', 'advanced', 'Web'),
('ML Sentiment Analyzer', 'Sentiment analysis using NLP', '["Machine Learning","Programming (Python)"]', 'advanced', 'AI');

-- 3. Career Roadmaps
INSERT INTO `career_roadmaps` (`career_goal`, `title`, `description`, `steps`, `skills_required`, `order_index`) VALUES
('software', 'Software Engineer Roadmap', 'Complete roadmap for aspiring software engineers', 
'["Master DSA fundamentals","Learn a programming language deeply","Build 2-3 portfolio projects","Practice on LeetCode/HackerRank","Prepare system design","Apply for internships"]',
'["Data Structures","Algorithms","Programming","Web Development"]', 1),
('government', 'Government Job Preparation', 'Roadmap for GATE, UPSC, and PSU exams',
'["Identify target exam (GATE/UPSC)","Create study schedule","Join coaching if needed","Practice previous papers","Stay updated with current affairs","Apply for notifications"]',
'["Quantitative Aptitude","Logical Reasoning","Verbal Ability","Domain Knowledge"]', 2),
('mba', 'MBA Preparation Roadmap', 'Path to top B-schools',
'["Take CAT/XAT/GMAT","Build strong profile","Prepare for GD-PI","Gain work experience","Research target colleges","Apply in multiple rounds"]',
'["Quantitative Aptitude","Logical Reasoning","Verbal Ability","Communication"]', 3),
('core', 'Core Engineering Career', 'Path for mechanical, civil, electrical engineers',
'["Master core subjects","Learn industry tools","Complete certifications","Build project portfolio","Network with professionals","Target PSUs and MNCs"]',
'["Domain Knowledge","Problem Solving","Technical Skills"]', 4),
('startup', 'Startup & Entrepreneurship', 'A roadmap for building your own business from idea to scale.',
'["Identify a Problem & Ideation","Market Research & Validation","Build an MVP (Minimum Viable Product)","Business Model Canvas & Finance","Legal & Registration","Marketing & User Acquisition","Pitching & Fundraising"]',
'["Leadership","Product Management","Sales & Marketing","Financial Planning","Networking"]', 5),
('research', 'Research & Higher Studies', 'Path for students interested in R&D, PhD, and academic careers.',
'["Identify Research Interests","Read Research Papers & Literature Review","Work on a Research Project","Publish Papers in Conferences/Journals","Prepare for GRE/GATE/NET","Apply to Labs/Universities"]',
'["Critical Thinking","Technical Writing","Data Analysis","Research Methodology","Mathematics"]', 6),
('other', 'Alternative Career Paths', 'For creative, non-traditional, and emerging career fields.',
'["Discover Your Passion & Strengths","Build a Portfolio/Showcase","Networking & Personal Branding","Freelancing or Internships","Continuous Learning & Upskilling","Find a Mentor"]',
'["Adaptability","Communication","Creativity","Self-Management","Networking"]', 7);
