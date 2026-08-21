//Модальные окна

/**
 * Modal windows on page bottom
 * @param element
 * @constructor
 */

var $page = jQuery('body');

function Modal(element) {
    this.$modalContainer = jQuery(element);
    this.$modal = this.$modalContainer.find('.js-modal');

    jQuery('.js-modal-open').on('click', this.onOpenModalClick.bind(this));
    jQuery('.js-modal-close').on('click', this.onCloseModalClick.bind(this));
    jQuery('.js-modal-close-all').on('click', this.closeAllModal.bind(this));
    this.$modal.on('openModal', this.onOpenModal.bind(this));
}

Modal.prototype.onOpenModal = function (ev) {
    this.openModal(jQuery(ev.currentTarget).attr('id'));
};

Modal.prototype.onOpenModalClick = function (ev) {
    ev.preventDefault();
    this.openModal(jQuery(ev.currentTarget).data('modal-id'));
};

Modal.prototype.onCloseModalClick = function (ev) {
    ev.preventDefault();
    this.closeModal(jQuery(ev.currentTarget).data('modal-id'));
};

Modal.prototype.openModal = function (id) {
    var $modal = this.$modal.filter('#' + id);
    if ($modal.length === 0) return;

    this.$modalContainer.fadeIn();

    this.$modal.removeClass('open');

    $modal.addClass('open');

    bodyLock()

};

Modal.prototype.closeModal = function (id) {
    var $modal = this.$modal.filter('#' + id);
    if ($modal.length === 0) return;
    $modal.removeClass('open');
    if (this.$modal.filter('open').length === 0) {
        this.$modalContainer.fadeOut();
    }
};

Modal.prototype.closeAllModal = function (id) {
    this.$modal.removeClass('open');
    this.$modalContainer.fadeOut();
    bodyUnLock()
};


jQuery.extend(jQuery.validator.messages, {
    required: "Пожалуйста, заполните поле",
    remote: "Введите правильное значение",
    email: "Введите корректный email адрес",
    url: "Введите корректный URL",
    date: "Введите корректную дату",
    dateISO: "Введите корректную дату в формате ISO",
    number: "Введите число",
    digits: "Введите только цифры",
    creditcard: "Введите правильный номер кредитной карты",
    equalTo: "Введите такое же значение ещё раз",
    extension: "Загрузите изображение, документ или архив",
    maxlength: jQuery.validator.format("Введите не больше {0} символов"),
    minlength: jQuery.validator.format("Введите не менее {0} символов"),
    rangelength: jQuery.validator.format("Введите значение длиной от {0} до {1} символов"),
    range: jQuery.validator.format("Введите число от {0} до {1}"),
    max: jQuery.validator.format("Введите число меньше {0}"),
    min: jQuery.validator.format("Введите число больше {0}"),
    phone: "Введите корректный номер телефона",
});

/** Form loader
*  @param form
*/

function formLoader(form) {
    var $form = jQuery(form);
    var isInitialized = $form.hasClass('form-initialized');
    if (isInitialized) return;
    $form.addClass('form-initialized');
    var controllerName = $form.data('controller');
    if (typeof this[controllerName] != 'function') controllerName = 'Form';
    new this[controllerName](form);
}

function Form(form) {
    this.init(form);
}

/* Form initialization */


Form.prototype.init = function (form) {
    this.$form = jQuery(form);
    this.$form.find('.input, .textarea').each(function () {
        new Input(this);
    });
    this.validater();
    // this.mask();
};
/* Form validation */


Form.prototype.validater = function () {

    this.validator = this.$form.validate({
        focusInvalid: true,
        highlight: function (element) {
            var $element = jQuery(element);
            var $row = $element.closest('.input-wrapper');
            $row.addClass('error');
        }.bind(this),
        unhighlight: function (element) {
            var $element = jQuery(element);
            var $row = $element.closest('.input-wrapper');
            $row.removeClass('error');
        }.bind(this),
        invalidHandler: function (e, validator) {
            var errors = validator.numberOfInvalids();
            console.log(errors);
        }.bind(this),
        submitHandler: function (el, ev) {
            if (this.$form.valid()) {
                this.submitForm(ev);
            } else {
                this.validator.focusInvalid();
            }
        }.bind(this),
    }
    );
};

/* Form mask */
$.jMaskGlobals.translation["d"] = $.jMaskGlobals.translation["9"];
delete $.jMaskGlobals.translation["9"];

Form.prototype.mask = function () {
    this.$form.find('input[type="tel"]').mask('+7 900 000 00 00');
};

/* Form submit handler */
Form.prototype.submitForm = function (ev) {
    var ajaxurl = '/wp-admin/admin-ajax.php';
    var formData = new FormData(this.$form[0]);
    // let selectarr = ['KUZOVFIND', 'KOROBKAFIND']; добавить все ьгдешзду name типов select
    // selectarr.forEach((value)=>{
    //     let selectfield = formData.getAll(value);
    //     if(selectfield.length){
    //         let allValue = '';
    //         selectfield.forEach((value)=>{
    //             allValue = allValue + value+'; ';
    //         });
    //         formData.append(value+'ARR', allValue);
    //     }
    // });
    for (var pair of formData.entries()) {
        console.log(pair[0]+ ', ' + pair[1]);
    }
    return false;
    jQuery.ajax({
        url: ajaxurl,
        method: 'post',
        action: formData.get('action'),
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            console.log(response);

            if (response.SUCCESS == 'Y') {
                if (response.HTML) {
                    jQuery(this.$form).html(response.HTML);
                }
            }

            if (response.SUCCESS == 'N') {
                if (response.MESSAGE) {
                    jQuery(this.$form).html(response.MESSAGE);
                }
            }
        }.bind(this)
    });
};

/**
 * Input element (add class for empty fields)
 * @param el
 * @constructor
 */
function Input(el) {
    this.$wrapper = jQuery(el);
    this.$input = this.$wrapper.find('input, textarea');
    this.$input.on('change', this.checkEmpty.bind(this));
    this.$input.on('input', this.checkEmpty.bind(this));

    this.checkEmpty();
}

Input.prototype.checkEmpty = function () {
    let val = this.$input.val();
    let placeholder = this.$input.attr('placeholder');
    this.$wrapper.toggleClass('empty', val.length === 0 && (!placeholder || placeholder.length === 0));
};

window.onload = initPage;

function initPage() {
    jQuery('form[data-controller]').each(function () { formLoader(this) });
    $page.find('.js-modal-container').each(function () {
        new Modal(this)
    });
}

const lockPadding = document.querySelectorAll('.lock-padding')
const wrapper = document.querySelector('.wrapper')
function bodyLock() {
    const lockPaddingValue = window.innerWidth - document.querySelector('.wrapper').offsetWidth + 'px';

    if (lockPadding.length > 0) {
        lockPadding.forEach((el) => {
            el.style.paddingRight = lockPaddingValue
        })
    }

    document.body.style.paddingRight = lockPaddingValue;

    document.body.classList.add('fixed');
}
function bodyUnLock() {
    document.body.style.paddingRight = '0'

    document.body.classList.remove('fixed')

    if (lockPadding.length > 0) {
        lockPadding.forEach((el) => {
            el.style.paddingRight = '0px'
        })
    }
}
const input = document.querySelector('.inputfile')
const clearInput = document.querySelector('.delete')
if (input) {
    let label = input.nextElementSibling,
        span = label.nextElementSibling
    input.addEventListener('change', function (e) {
        clearInput.classList.add('active')
        let fileName = '';
        fileName = e.target.value.split('\\').pop();
        span.innerHTML = fileName;
    });
    clearInput.addEventListener('click', () => {
        input.value = "";
        span.innerHTML = "Прикрепить файл";
        clearInput.classList.remove('active')
    })
}

var x, i, j, l, ll, selElmnt, a, b, c;
x = document.getElementsByClassName("custom-select");
l = x.length;
for (i = 0; i < l; i++) {
    selElmnt = x[i].getElementsByTagName("select")[0];
    ll = selElmnt.length;
    a = document.createElement("DIV");
    a.setAttribute("class", "select-selected");
    a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
    x[i].appendChild(a);
    b = document.createElement("DIV");
    b.setAttribute("class", "select-items select-hide");
    for (j = 0; j < ll; j++) {
        c = document.createElement("DIV");
        c.innerHTML = selElmnt.options[j].innerHTML;
        c.addEventListener("click", function (e) {
            var y, i, k, s, h, sl, yl;
            s = this.parentNode.parentNode.getElementsByTagName("select")[0];
            sl = s.length;
            h = this.parentNode.previousSibling;
            console.log(s);
            for (i = 0; i < sl; i++) {
                if (s.options[i].innerHTML == this.innerHTML) {
                    s.selectedIndex = i;
                    let m = selElmnt.querySelectorAll('option');
                    console.log(m[s.selectedIndex]);
                    let valsel=m[s.selectedIndex];
                    console.log('valsel',valsel);
                    valsel2=valsel.getAttribute('data-lang');
                    // eraseCookie('googtrans');
                    console.log(valsel2);
                    if(typeof doGTranslate==='function'){

                        console.log('start');
                        doGTranslate(valsel2);
                    } else {
                        console.log('error lang');
                    }
                    h.innerHTML = this.innerHTML;
                    y = this.parentNode.getElementsByClassName("same-as-selected");
                    yl = y.length;
                    for (k = 0; k < yl; k++) {
                        y[k].removeAttribute("class");
                    }
                    this.setAttribute("class", "same-as-selected");
                    break;
                }
            }
            h.click();
        });
        b.appendChild(c);
    }
    x[i].appendChild(b);
    a.addEventListener("click", function (e) {
        e.stopPropagation();
        closeAllSelect(this);
        this.nextSibling.classList.toggle("select-hide");
        this.classList.toggle("select-arrow-active");
    });
}

function closeAllSelect(elmnt) {
    var x, y, i, xl, yl, arrNo = [];
    x = document.getElementsByClassName("select-items");
    y = document.getElementsByClassName("select-selected");
    xl = x.length;
    yl = y.length;
    for (i = 0; i < yl; i++) {
        if (elmnt == y[i]) {
            arrNo.push(i)
        } else {
            y[i].classList.remove("select-arrow-active");
        }
    }
    for (i = 0; i < xl; i++) {
        if (arrNo.indexOf(i)) {
            x[i].classList.add("select-hide");
        }
    }
}
document.addEventListener("click", closeAllSelect);

const searchBtn = document.querySelector('.header__search-btn')
const searchForm = document.querySelector('.header__search form')
if (searchBtn) {
    searchBtn.addEventListener('click', () => {
        searchForm.classList.toggle('active')
    })
}
// document.addEventListener('mousedown', function (e) {
//     if (e.target.closest('.header__search') === null) {
//         searchForm.classList.remove('active')
//     }
// });

