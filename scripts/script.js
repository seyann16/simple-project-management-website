// Handle registration form
if (document.getElementById('registerForm')) {
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log("Registration form submitted");
        
        const formData = {
            action: 'register',
            username: document.getElementById('username').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        };

        console.log("Sending registration request...", formData);

        fetch('../../api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            console.log("Response status:", response.status);
            return response.text(); // First get as text to see what's returned
        })
        .then(text => {
            console.log("Raw response:", text);
            try {
                const data = JSON.parse(text);
                console.log("Parsed data:", data);
                
                const messageDiv = document.getElementById('message');
                if (messageDiv) {
                    messageDiv.textContent = data.message;
                    messageDiv.className = data.success ? 'message success' : 'message error';
                }
                
                if (data.success) {
                    alert('Registration successful! Redirecting to login page...');
                    setTimeout(() => {
                        window.location.href = '/project-management/pages/html/login.html';
                    }, 1000);
                } else {
                    alert('Registration failed: ' + data.message);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Raw response that failed to parse:', text);
                alert('Server returned invalid response. Check console for details.');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Network error: ' + error.message);
        });
    });
}

// Handle login form
if (document.getElementById('loginForm')) {
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'login',
            username: document.getElementById('username').value,
            password: document.getElementById('password').value
        };

        fetch('../../api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                
                const messageDiv = document.getElementById('message');
                if (messageDiv) {
                    messageDiv.textContent = data.message;
                    messageDiv.className = data.success ? 'message success' : 'message error';
                }
                
                if (data.success) {
                    alert('Login successful! Redirecting to dashboard...');
                    setTimeout(() => {
                        window.location.href = '../php/dashboard.php';
                    }, 1000);
                } else {
                    alert('Login failed: ' + data.message);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Raw response:', text);
                alert('Server returned invalid response.');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Network error: ' + error.message);
        });
    });
}