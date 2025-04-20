// Session timeout warning
let idleTime = 0;
const idleInterval = setInterval(timerIncrement, 60000); // 1 minute

// Reset idle time on user activity
document.addEventListener('mousemove', resetIdleTime);
document.addEventListener('keypress', resetIdleTime);

function resetIdleTime() {
    idleTime = 0;
}

function timerIncrement() {
    idleTime++;
    const timeoutMinutes = Math.floor(<?php echo SESSION_TIMEOUT; ?> / 60);
    const warningMinutes = Math.floor(timeoutMinutes * 0.8); // Warn at 80% of timeout
    
    if (idleTime >= warningMinutes && idleTime < timeoutMinutes) {
        const remaining = timeoutMinutes - idleTime;
        Swal.fire({
            title: 'Session Timeout Warning',
            text: Your session will expire in ${remaining} minute(s). Move your mouse or press a key to stay logged in.,
            icon: 'warning',
            timer: 5000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    } else if (idleTime >= timeoutMinutes) {
        clearInterval(idleInterval);
        window.location.href = '../logout.php';
    }
}

// Password validation
document.addEventListener('DOMContentLoaded', function() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(field => {
        field.addEventListener('input', function() {
            const password = this.value;
            const validation = validatePassword(password);
            
            if (validation !== true) {
                this.setCustomValidity(validation);
                this.reportValidity(); // Add this to show validation message immediately
            } else {
                this.setCustomValidity('');
            }
        });
    });

    // Add form validation on submit
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const passwordFields = this.querySelectorAll('input[type="password"]');
            let isValid = true;
            
            passwordFields.forEach(field => {
                const validation = validatePassword(field.value);
                if (validation !== true) {
                    field.setCustomValidity(validation);
                    field.reportValidity();
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
});

function validatePassword(password) {
    const minLength = <?php echo PASSWORD_MIN_LENGTH; ?>;
    
    if (password.length < minLength) {
        return Password must be at least ${minLength} characters long.;
    }
    if (!/[A-Z]/.test(password)) {
        return "Password must contain at least one uppercase letter.";
    }
    if (!/[a-z]/.test(password)) {
        return "Password must contain at least one lowercase letter.";
    }
    if (!/[0-9]/.test(password)) {
        return "Password must contain at least one number.";
    }
    if (!/[^A-Za-z0-9]/.test(password)) {
        return "Password must contain at least one special character.";
    }
    return true;
}