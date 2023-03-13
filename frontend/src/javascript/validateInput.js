
export function validateLength(field, value, min, max) {
    console.log(value)
    if (!value || !value.length) {
        return `${field} cannot be empty`;
    }

    value = value.trim()
    const length = value.length

    if (length < min) {
        return `${field} must be a minimum of ${min} characters`;
    }
    if (length > max) {
        return `${field} must be a maximum of ${max} (inclusive) characters`;
    }
    return null
}

export function validateInput(field, value, min, max, required = true) {
    console.log(`validateInput: input: ${value}`)
    const passedLength = validateLength(field, value, min, max)
    console.log(passedLength)
    if (passedLength != null) {
        return passedLength
    }

    if (required) {

    }
    return null
}

export function validateEmail(field, value) {
    console.log(/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/.test(value))
    if (!/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/.test(value)) {
        return `${field} is not in the correct format`;
    }
    return null
}


export function checkEmpty(inputs, required) {
    for (const [key, value] of Object.entries(inputs)) {
        console.log(`${key}: ${value}`)
        if (required.includes(key)) {
            if (!value) {
                return `${key} cannot be empty`
            }
        }
    }
    return null
}


export function validateForm(form, validationRules) {
    for (const [key, value] of Object.entries(form)) {
        console.log(`${key}: ${value}`)
        const validation = validationRules[key]
        console.log(validation)
        if ((value == null || value.length == 0) && !(validation.hasOwnProperty('required') ? validation.required : true)) {
            continue
        }
        if (key.includes('email')) {
            const validated = validateEmail(key, value)
            if (validated) {
                return validated
            }
        } else if (typeof (validation) == "object") {
            const validated = validateInput(key, value, validation.min, validation.max)
            if (validated) {
                return validated
            }
        }
        else {
            continue
        }

    }
    return null
}

export function validatePasswords(password, confirmPassword) {
    if (password.localeCompare(confirmPassword) != 0) {
        return 'Passwords do not match'
    }
    return null
}