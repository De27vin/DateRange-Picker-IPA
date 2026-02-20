window.livewire.onError(statusCode => {
    if (statusCode === 419) {
        window.location.href = "{{ route('login') }}"
    } else {
        return false
    }
})


// // Set the idle time threshold (in milliseconds)
// const IDLE_TIME_THRESHOLD = 3600000; // 60 minutes
//
// let idleTimer;
//
// // Start the idle timer
// function startIdleTimer() {
//   idleTimer = setTimeout(logoutUser, IDLE_TIME_THRESHOLD);
// }
//
// // Reset the idle timer
// function resetIdleTimer() {
//   clearTimeout(idleTimer);
//   startIdleTimer();
// }
//
// // Log out the user
// function logoutUser() {
//   // Perform the logout operation, e.g., redirect to the logout page or send an API request to invalidate the session
//   window.location.href = '/logout';
// }
//
// // Add event listeners to detect user activity
// document.addEventListener('mousemove', resetIdleTimer);
// document.addEventListener('keydown', resetIdleTimer);
// document.addEventListener('click', resetIdleTimer);
//
// startIdleTimer();
