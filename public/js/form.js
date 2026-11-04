document.addEventListener('DOMContentLoaded', () => {
    const passwordTogglers = document.querySelectorAll('.password-toggler');

    if (passwordTogglers) {
        for (const toggler of passwordTogglers) {
            const targetId = toggler.dataset.togglePassword;
            const target = document.querySelector(`#${targetId}`);

            if (target) {
                toggler.addEventListener('change', (event) => {
                    target.type = event.target.checked ? 'text' : 'password';
                })
            }
        }
    }
})