// Future animations or sidebar toggles
console.log("GSIAS JS loaded");
// Dark Mode Toggle
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('themeToggle');
    if(toggle){
        toggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
        });
    }
});