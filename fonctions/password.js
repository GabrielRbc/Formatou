const submitBtn = document.getElementById('btnInscription');
const passwordInput = document.getElementById('motdepasse');

if (passwordInput && submitBtn) {

    const rules = {
        length: document.getElementById('rule-length'),
        upper: document.getElementById('rule-upper'),
        lower: document.getElementById('rule-lower'),
        number: document.getElementById('rule-number'),
        special: document.getElementById('rule-special')
    };

    passwordInput.addEventListener('input', function () {
        const value = passwordInput.value;

        const checks = {
            length: value.length >= 8,
            upper: /[A-Z]/.test(value),
            lower: /[a-z]/.test(value),
            number: /[0-9]/.test(value),
            special: /[^A-Za-z0-9]/.test(value)
        };

        toggleRule(rules.length, checks.length);
        toggleRule(rules.upper, checks.upper);
        toggleRule(rules.lower, checks.lower);
        toggleRule(rules.number, checks.number);
        toggleRule(rules.special, checks.special);

        // Active/désactive le bouton
        submitBtn.disabled = !Object.values(checks).every(Boolean);
    });

    function toggleRule(element, isValid) {
        if (isValid) {
            element.classList.remove('text-danger');
            element.classList.add('text-success');
            element.innerHTML = element.innerHTML.replace('❌', '✔️');
        } else {
            element.classList.remove('text-success');
            element.classList.add('text-danger');
            element.innerHTML = element.innerHTML.replace('✔️', '❌');
        }
    }

    const togglePassword = document.getElementById('togglePassword');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('change', function () {
            passwordInput.type = this.checked ? 'text' : 'password';
        });
    }
}