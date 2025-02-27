// Written by Ping-Chieh Tu and Lucas Renaudie
function isAlphaNumeric(str) {
    var code, i, len;
    for (i = 0, len = str.length; i < len; i++) {
        code = str.charCodeAt(i);
        if (!(code > 47 && code < 58) && // numeric (0-9)
            !(code > 64 && code < 91) && // upper alpha (A-Z)
            !(code > 96 && code < 123)) { // lower alpha (a-z)
            return false;
        }
    }
    return true;
};

function isNumeric(str) {
    var code, i, len;
    for (i = 0, len = str.length; i < len; i++) {
        code = str.charCodeAt(i);
        if (!(code > 47 && code < 58)) { // numeric (0-9)
            return false;
        }
    }
    return true;
}

function regFormValid() {
    let fname = document.forms["reg-form"]["reg-firstname"].value;
    let lname = document.forms["reg-form"]["reg-lastname"].value;
    let email = document.forms["reg-form"]["reg-email"]?.value || "";
    let mcgill_email = document.forms["reg-form"]["reg-mcgill-email"]?.value || "";
    let pass = document.forms["reg-form"]["reg-password"].value;
    let cpass = document.forms["reg-form"]["reg-confirm-password"].value;
    let phone = document.forms["reg-form"]["reg-phone"]?.value || "";

    let warningDiv = document.getElementById("reg-warning");
    warningDiv.innerHTML = "";

    // check input non empty
    if (!fname || !lname || (!email && !mcgill_email) || !pass || !cpass) {
        warningDiv.innerHTML = `<p style="color: red;">*Please fill in all required fields.</p>`;
        warningDiv.style.display = "flex";
        return false;
    }

    // alphanumeric check
    if (!isAlphaNumeric(fname) || !isAlphaNumeric(lname) || !isAlphaNumeric(pass)) {
        warningDiv.innerHTML = `<p style="color: red;">*Please only enter numbers 0-9 and alphabets a-z, A-Z</p>`;
        warningDiv.style.display = "flex";
        return false;
    }

    // check password
    if (pass !== cpass) {
        // put warning
        warningDiv.innerHTML = `<p style="color: red;">*Passwords do not match. Please confirm your password.</p>`;
        warningDiv.style.display = "flex";
        return false;
    }

    // phone number check
    if (phone !== "") {
        if (!isNumeric(phone) || phone.length !== 10) {
            warningDiv.innerHTML = `<p style="color: red;">*Phone number should be 10 digits</p>`;
            warningDiv.style.display = "flex";
            return false;
        }
    }
    // need password to be length 16
    if (pass.length > 16) {
        warningDiv.innerHTML = `<p style="color: red;">*Password must be under length of 16</p>`;
        warningDiv.style.display = "flex";
        return false;
    }
    // fname, lname to be length 80
    if (fname.length > 80) {
        warningDiv.innerHTML = `<p style="color: red;">*First name must be under length of 80</p>`;
        warningDiv.style.display = "flex";
        return false;
    }
    if (lname.length > 80) {
        warningDiv.innerHTML = `<p style="color: red;">*Last name must be under length of 16</p>`;
        warningDiv.style.display = "flex";
        return false;
    }

    // email length must be under 255
    if (email.length > 255) {
        warningDiv.innerHTML = `<p style="color: red;">*Email must be under length of 255</p>`;
        warningDiv.style.display = "flex";
        return false;
    }

    return true;
}

function logFormValid() {
    let email = document.forms["log-form"]["email"].value;
    let pass = document.forms["log-form"]["password"].value;

    const loginprompt = document.getElementById('log-prompt');
    const passprompt = document.getElementById('passprompt');
    const existprompt = document.getElementById('user-not-exist');
    const sregprompt = document.getElementById('sregprompt');

    loginprompt.style.display = 'none';
    existprompt.style.display = 'none';
    passprompt.style.display = 'none';
    sregprompt.style.display = 'none';

    let warningDiv = document.getElementById("log-warning");
    warningDiv.innerHTML = "";

    if (!email || !pass) {
        // put warning
        warningDiv.innerHTML = `<p style="color: red;">*Please fill in all required fields.</p>`;
        warningDiv.style.display = "flex";
        return false;
    }

    if (!isAlphaNumeric(pass)) {
        // put warning
        warningDiv.innerHTML = `<p style="color: red;">*Please only enter numbers 0-9 and alphabets a-z, A-Z</p>`;
        warningDiv.style.display = "flex";
        return false;
    }
    return true;
}
