document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("rag-toggle-btn");
    const chatWindow = document.getElementById("rag-chat-window");
    const closeBtn = document.getElementById("rag-close-btn");
    const chatForm = document.getElementById("rag-chat-form");
    const chatInput = document.getElementById("rag-chat-input");
    const chatBody = document.getElementById("rag-chat-body");
    const faqsContainer = document.getElementById("rag-faqs-container");

    if (!toggleBtn || !chatWindow) return; // Prevent errors if not logged in

    let isOpen = false;
    let isWaiting = false;

    // The magical array storing our current conversation limits
    let chatHistory = []; 

    // Retrieve environment variables injected by PHP
    const config = window.RAG_CONFIG || {};
    const RAG_API_URL = config.api_url || "http://localhost:8000/api/chat";
    const RAG_TOKEN = config.token || "";
    const RAG_USER_CONTEXT = config.user_context || null;

    // Toggle logic with pulse animation handler
    toggleBtn.addEventListener("click", (e) => {
        e.preventDefault();
        isOpen = !isOpen;
        if (isOpen) {
            chatWindow.classList.remove("d-none");
            toggleBtn.classList.remove("pulse-animation");
            setTimeout(() => chatInput.focus(), 300);
        } else {
            chatWindow.classList.add("d-none");
            toggleBtn.classList.add("pulse-animation");
        }
    });

    closeBtn.addEventListener("click", (e) => {
        e.preventDefault();
        isOpen = false;
        chatWindow.classList.add("d-none");
        toggleBtn.classList.add("pulse-animation");
    });

    // FAQ Pills logic
    const faqBtns = document.querySelectorAll(".rag-faq-btn");
    faqBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            if (isWaiting) return;
            const question = btn.innerText;
            processUserMessage(question);
        });
    });

    // Form submit
    chatForm.addEventListener("submit", (e) => {
        e.preventDefault();
        if (isWaiting) return;
        
        const question = chatInput.value.trim();
        if (!question) return;
        
        processUserMessage(question);
    });

    function appendMessage(role, content) {
        const msgDiv = document.createElement("div");
        msgDiv.className = `rag-message ${role} d-flex flex-column align-items-${role === 'user' ? 'end' : 'start'}`;
        
        const contentDiv = document.createElement("div");
        contentDiv.className = `msg-content p-2 px-3 rounded-4 shadow-sm text-dark d-inline-block`;
        // Format markdown very simply: replace **text** with bold, \n with <br>
        let formatted = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        formatted = formatted.replace(/\n/g, '<br>');
        
        contentDiv.innerHTML = formatted;
        msgDiv.appendChild(contentDiv);
        chatBody.appendChild(msgDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
        
        // Return element in case we want to stream text into it
        return contentDiv; 
    }

    function showProgressIndicator() {
        const msgDiv = document.createElement("div");
        msgDiv.className = `rag-message bot progress-msg d-flex flex-column align-items-start`;
        msgDiv.id = "rag-progress-indicator";
        
        msgDiv.innerHTML = `
            <div class="msg-content p-2 px-3 rounded-4 shadow-sm text-dark d-inline-block typing-indicator">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <span class="progress-text" id="rag-progress-text">Analyzing query...</span>
            </div>
        `;
        chatBody.appendChild(msgDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function hideProgressIndicator() {
        const indicator = document.getElementById("rag-progress-indicator");
        if (indicator) {
            indicator.remove();
        }
    }

    // High-end character-by-character typewriter streaming simulation
    function typeWriterStream(element, text, cb) {
        element.innerHTML = ""; // Clear
        let i = 0;
        
        // Simple HTML markdown parser for typewriter 
        let parsedText = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        parsedText = parsedText.replace(/\n/g, '<br>');
        
        // We type HTML by creating a temp div, converting string to array of nodes/text, but 
        // for simplicity and speed, we will dangerously type chars. If inside <tag>, dump full tag.
        
        let isTag = false;
        let pText = "";
        
        function typeChar() {
            if (i < parsedText.length) {
                let char = parsedText.charAt(i);
                
                if (char === '<') isTag = true;
                
                pText += char;
                element.innerHTML = pText;
                i++;
                
                if (isTag) {
                    if (char === '>') isTag = false;
                    typeChar(); // type immediately without delay for HTML tags
                } else {
                    // Randomize typing speed for human feel (10ms to 30ms)
                    chatBody.scrollTop = chatBody.scrollHeight;
                    setTimeout(typeChar, Math.random() * 20 + 10);
                }
            } else {
                if(cb) cb();
            }
        }
        typeChar();
    }

    // Dynamic context router based on user question keywords
    function getContextForQuestion(question) {
        const sections = config.user_context_sections || {};
        if (!sections.basic) return config.user_context || ""; // fallback
        
        const q = question.toLowerCase();
        let activeParts = [];
        
        // Always include basic student profile
        if (sections.basic) activeParts.push(sections.basic);
        
        // Projects context
        if (q.includes("project") || q.includes("portfolio") || q.includes("github") || q.includes("submit")) {
            if (sections.projects) activeParts.push(sections.projects);
        }
        
        // Tasks context
        if (q.includes("task") || q.includes("priority") || q.includes("todo") || q.includes("roadmap") || q.includes("complete") || q.includes("pending")) {
            if (sections.tasks) activeParts.push(sections.tasks);
        }
        
        // Readiness & Scores context
        if (q.includes("readiness") || q.includes("score") || q.includes("eligibility") || q.includes("ready") || q.includes("improve") || q.includes("interview")) {
            if (sections.readiness) activeParts.push(sections.readiness);
            if (sections.skills) activeParts.push(sections.skills);
            if (sections.tests) activeParts.push(sections.tests);
            if (sections.academics) activeParts.push(sections.academics);
        }
        
        // Skills context
        if (q.includes("skill") || q.includes("learn") || q.includes("language") || q.includes("technology")) {
            if (sections.skills) activeParts.push(sections.skills);
        }
        
        // Applications context
        if (q.includes("apply") || q.includes("application") || q.includes("job") || q.includes("placement") || q.includes("company") || q.includes("hiring")) {
            if (sections.applications) activeParts.push(sections.applications);
            if (sections.readiness) activeParts.push(sections.readiness);
        }
        
        // Mentorship context
        if (q.includes("mentor") || q.includes("guide") || q.includes("connect") || q.includes("session")) {
            if (sections.mentorship) activeParts.push(sections.mentorship);
        }
        
        // Fallback: if no keyword matches but they ask a question (not a greeting)
        const isGreeting = /^(hi|hello|hey|yo|greetings|good morning|good afternoon|good evening)\b/i.test(q);
        if (activeParts.length === 1 && !isGreeting) {
            if (sections.readiness) activeParts.push(sections.readiness);
            if (sections.tasks) activeParts.push(sections.tasks);
        }
        
        return activeParts.join("\n\n");
    }

    async function processUserMessage(question) {
        chatInput.value = "";
        isWaiting = true;
        chatInput.disabled = true;
        
        // 1. Show user message
        appendMessage("user", question);
        
        // 2. Hide FAQs container visually to clear space
        faqsContainer.style.display = "none";
        
        // 3. Show Dynamic Progress
        showProgressIndicator();
        const pText = document.getElementById("rag-progress-text");
        
        // Simulate progress intervals
        const progressTimeouts = [
            setTimeout(() => { if (pText) pText.innerText = "Fetching context & user data..."; }, 1500),
            setTimeout(() => { if (pText) pText.innerText = "Analyzing information..."; }, 3500),
            setTimeout(() => { if (pText) pText.innerText = "Generating response, please be patient..."; }, 6000)
        ];

        try {
            // FIRE HTTP REQUEST!
            const payload = {
                question: question,
                user_context: getContextForQuestion(question),
                history: chatHistory
            };

            const response = await fetch(RAG_API_URL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${RAG_TOKEN}`
                },
                body: JSON.stringify(payload)
            });

            progressTimeouts.forEach(clearTimeout);
            hideProgressIndicator();

            if (!response.ok) {
                const textErr = await response.text();
                throw new Error(response.status + " " + textErr);
            }

            const data = await response.json();
            const answer = data.answer || "I'm sorry, I couldn't formulate a response.";

            // Save to memory arrays
            chatHistory.push({ role: "user", content: question });
            chatHistory.push({ role: "assistant", content: answer });

            // Ensure we don't hold thousands of arrays on front-end memory
            if (chatHistory.length > 20) chatHistory = chatHistory.slice(-20);

            // Create blank bot bubble and run TypeWriter on it natively
            const fakeBubble = appendMessage("bot", "");
            typeWriterStream(fakeBubble, answer, () => {
                isWaiting = false;
                chatInput.disabled = false;
                chatInput.focus();
            });

        } catch (error) {
            progressTimeouts.forEach(clearTimeout);
            hideProgressIndicator();
            console.error("RAG Error:", error);
            appendMessage("bot", "I am currently offline or experiencing a connection issue. Please ensure the backend is running or try again later.");
            isWaiting = false;
            chatInput.disabled = false;
        }
    }
});
