document.addEventListener('DOMContentLoaded', () => {
    const sideMenu = document.querySelector('.bottom-header');
    const toggleButton = document.getElementById('toggle-button');
    const closeButton = document.querySelector('.close-button');

    if (sideMenu && toggleButton) {
        toggleButton.addEventListener('click', () => {
            sideMenu.classList.add('open');  
        });

        closeButton.addEventListener('click', () => {
            sideMenu.classList.remove('open'); 
        });
    }
});