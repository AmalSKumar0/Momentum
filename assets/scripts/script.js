// 1. Modal Logic
function toggleModal() {
    const modal = document.getElementById('questModal');
    // Safety check: ensure modal exists before toggling
    if (modal) modal.classList.toggle('active');
}

// Close when clicking outside card (on the backdrop)
const questModal = document.getElementById('questModal');
if (questModal) {
    questModal.addEventListener('click', function(e) {
        if (e.target === this) {
            toggleModal();
        }
    });
}

window.showLoading = () => {
    const content = document.getElementById('modalContent');
    if (content) content.innerHTML = `<div class="loading-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>`;
}

// 2. Complete Quest Logic (Improved)
window.completeQuest = (btn) => {
    btn.innerHTML = `<i class="fas fa-check"></i> Done`;
    btn.style.background = "#b2bec3";
    btn.style.boxShadow = "none";
    btn.style.pointerEvents = "none"; // Prevent double clicking
    
    // FIXED: Use .closest() for safer DOM traversal
    // Ensure your HTML card has a class like 'quest-card' or 'task-item'
    const card = btn.closest('.card') || btn.parentElement.parentElement; 
    if (card) card.style.opacity = "0.6";
}

// 3. Sidebar & Overlay Logic
window.toggleSidebar = () => {
    const sb = document.getElementById('sidebar');
    const ov = document.getElementById('overlay');
    
    if (!sb || !ov) return; // Safety check

    sb.classList.toggle('active');
    
    if (sb.classList.contains('active')) {
        ov.classList.add('show', 'sidebar-open');
    } else {
        ov.classList.remove('show', 'sidebar-open');
    }
}

const overlay = document.getElementById('overlay');
if (overlay) {
    overlay.addEventListener('click', () => {
        if (overlay.classList.contains('sidebar-open')) {
            window.toggleSidebar();
        } else {
            // FIXED: Changed closeModal() to toggleModal()
            toggleModal(); 
        }
    });
}

// 4. Calendar Logic
document.addEventListener('DOMContentLoaded', () => {
    const monthList = document.getElementById('monthList');
    const dateStrip = document.getElementById('dateStrip');
    
    if (!monthList || !dateStrip) return; // Stop if elements don't exist

    const now = new Date();
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const daysShort = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

    // Render Months
    months.forEach((m, i) => {
        const s = document.createElement('span');
        s.style.margin = "0 10px";
        s.style.cursor = "pointer";
        s.textContent = m;
        
        // Active Month Style
        if (i === now.getMonth()) {
            s.style.color = "var(--primary)";
            s.style.fontSize = "1.4rem";
            s.style.fontWeight = "bold";
        } else {
            s.style.color = "#b2bec3";
            s.style.fontSize = "0.9rem";
        }
        monthList.appendChild(s);
    });

    // Render Date Strip (-4 days to +10 days)
    for (let i = -4; i <= 10; i++) {
        const date = new Date();
        date.setDate(now.getDate() + i); // Automatically handles month rollover
        
        const card = document.createElement('div');
        card.classList.add('date-card');
        if (i === 0) card.classList.add('active');

        card.innerHTML = `
            <span class="date-num">${date.getDate()}</span>
            <span class="date-day">${daysShort[date.getDay()]}</span>
        `;
        
        dateStrip.appendChild(card);
    }

    // Scroll to today on load
    setTimeout(() => {
        const active = dateStrip.querySelector('.active');
        if (active) active.scrollIntoView({ behavior: 'smooth', inline: 'center' });
    }, 100);
});