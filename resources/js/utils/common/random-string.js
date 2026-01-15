/**
 * Generate a random string with specific requirements (e.g., uppercase, number, special character)
 * @param {number} length Length of the string (default 8)
 * @param {boolean} hasUppercase Whether the string should contain at least 1 uppercase letter (default true)
 * @param {boolean} hasNumber Whether the string should contain at least 1 number (default true)
 * @param {boolean} hasSpecialChar Whether the string should contain at least 1 special character (default true)
 * @returns {string} A randomly generated string
 */
export function generateRandomString(length = 8, hasUppercase = true, hasNumber = true, hasSpecialChar = true) {
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*(),.?\":{}|<>";
    let result = '';
    let checkUppercase = false;
    let checkNumber = false;
    let checkSpecialChar = false;

    // Generate string with required conditions
    while (!(checkUppercase && checkNumber && checkSpecialChar)) {
        result = Array(length)
            .fill(charset)
            .map(x => x[Math.floor(Math.random() * x.length)])
            .join('');

        checkUppercase = hasUppercase ? /[A-Z]/.test(result) : true;
        checkNumber = hasNumber ? /[0-9]/.test(result) : true;
        checkSpecialChar = hasSpecialChar ? /[!@#$%^&*(),.?":{}|<>]/.test(result) : true;
    }

    return result;
}
