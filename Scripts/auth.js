(function() {
    var body = document.body;
    var buttonToggle = document.getElementsByClassName('container')[0];
    var buttonReg = document.getElementsByClassName('button-reg')[0];

    var isRequired = true; // Флаг текущего состояния (true - required установлен, false - не установлен)

    buttonReg.addEventListener('click', function toggleClasses() {
        [body, buttonReg].forEach(function (el) {
            el.classList.toggle('button');

            // Найдем поля
            var mailInput = document.querySelector('.mail-enter');
            var passwordInput = document.querySelector('.second-password');
            var conditionsCheckbox = document.querySelector('.check-conditions');

            // Добавим или уберем атрибут required у полей в зависимости от текущего состояния
            if (mailInput) {
                if (!isRequired) {
                    mailInput.setAttribute('required', 'true');
                } else {
                    mailInput.removeAttribute('required');
                }
            }

            if (passwordInput) {
                if (!isRequired) {
                    passwordInput.setAttribute('required', 'true');
                } else {
                    passwordInput.removeAttribute('required');
                }
            }

            if (conditionsCheckbox) {
                if (!isRequired) {
                    conditionsCheckbox.setAttribute('required', 'true');
                } else {
                    conditionsCheckbox.removeAttribute('required');
                }
            }
        });

        // Изменяем состояние флага после каждого клика
        isRequired = !isRequired;
    });
})();