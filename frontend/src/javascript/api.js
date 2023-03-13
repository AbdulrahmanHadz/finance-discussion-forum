import axios from 'axios';

const apiUrl = import.meta.env.VITE_API_URL

export function convertQueryString(data) {
    if (data == null) {
        return null
    }
    const queryString = new URLSearchParams();

    Object.entries(data).forEach(([key, value]) => {
        if (Array.isArray(value)) {
            value.forEach(value => queryString.append(`${key}[]`, value.toString()))
        } else if (typeof value == "object") {
            if (value == null) {
                return
            }

            Object.entries(value).forEach(([object_key, object_value]) => {
                queryString.append(`${key}[${object_key}]`, object_value.toString())
            })
        } else {
            queryString.append(key, value.toString())
        }
    });

    return queryString.toString()
}


async function requestAPI(method, route, params = null, data = null) {
    const config = {
        method: method,
        url: route
    }

    if (params) {
        config.params = params
    }

    if (data) {
        config.data = data
    }

    const response = axios(config)
    console.log(response)
    return response
}

export function fetchData(route, params = null) {
    console.log(`GETing route ${route} with params ${params}`)
    return requestAPI("GET", `${apiUrl}/${route}`, params = params)
}

export function postData(route, data = null) {
    const formattedData = convertQueryString(data)
    console.log(`POSTing ${formattedData} to route ${route}`)
    return requestAPI("POST", `${apiUrl}/${route}`, null, data = formattedData)
}

export function updateData(route, id, data = null) {
    const formattedData = convertQueryString(data)
    console.log(`PUTing ${formattedData} to route ${route}/${id}`)
    return requestAPI("PUT", `${apiUrl}/${route}/${id}`, null, data = formattedData)
}

export function deleteData(route, id, data = null) {
    const formattedData = convertQueryString(data)
    console.log(`DELTEing ${route}/${id}`)
    return requestAPI("DELETE", `${apiUrl}/${route}/${id}`, null, data = formattedData)
}
