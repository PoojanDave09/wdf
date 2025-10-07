// session-timeout.js

// Timeout length in milliseconds
const SESSION_TIMEOUT = 900000; // 15 minutes

let timeout;

function resetTimeout() {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    alert("Session expired due to inactivity. You will be logged out.");
    // Redirect to logout or login page:
    window.location.href = "logout.php";
  }, SESSION_TIMEOUT);
}

// Listen for user activity (mouse, keyboard)
window.onload = resetTimeout;
window.onmousemove = resetTimeout;
window.onkeypress = resetTimeout;
window.onclick = resetTimeout;
window.onscroll = resetTimeout;
