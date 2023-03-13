import { forceRerenderHeaderKey } from './keys.js';

export function isLoggedIn() {
    const localstorageData = getLocalStorage()
    if (localstorageData) {
        const expired = localstorageData.hasOwnProperty('expiry') ? checkExpired(localstorageData.expiry) : false
        const loggedIn = localstorageData.hasOwnProperty('id') ? true : false

        if (!loggedIn || !expired) {
            return false
        }

        return true
    }
    return false
}

export function loggedInId() {
    const localstorageData = getLocalStorage()
    if (localstorageData) {
        const userId = localstorageData.hasOwnProperty('id') ? localstorageData.id : null
        return userId
    }
    return null
}

export function loggedInUsername() {
    const localstorageData = getLocalStorage()
    if (localstorageData) {
        const username = localstorageData.hasOwnProperty('username') ? localstorageData.username : null
        return username
    }
    return null
}


function getTimestampInSeconds(date) {
    return Math.floor(date / 1000)
}

function checkExpired(date) {
    const now = new Date();
    now.setHours(0, 0, 0, 0);
    if (date < now) return true
    return false
}

export function setLocalStorageExpiry(id, username) {
    const day = new Date();
    day.setDate(day.getDate() + 30);
    const date = getTimestampInSeconds(day)
    localStorage.setItem('login', JSON.stringify({ 'id': id, 'expiry': date, 'username': username.trim() }))
    console.log(JSON.stringify(localStorage.getItem('login')))
    forceRerenderHeaderKey()
}

export function getLocalStorage() {
    return JSON.parse(localStorage.getItem('login'))
}

export function clearLocalStorage() {
    localStorage.removeItem('login')
    forceRerenderHeaderKey()
}

export function goToLogin() {
    if (!isLoggedIn()) {
        this.$router.push('/login')
    }
}