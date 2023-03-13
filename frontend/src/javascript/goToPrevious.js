
export function goBack(router) {
    if (window.history.length > 2) {
        router.go(-1)
    } else {
        router.push('/home')
    }
}