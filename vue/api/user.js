import request from '../api/utils/request'

/**
 * Lấy thông tin kì thi, ca thi, phòng thi
 * @returns
 */
export function login(data) {
    return request({
        url: 'api/login',
        method: 'POST',
        data: data
    })
}

/**
 * Lấy thông tin kì thi, ca thi, phòng thi
 * @returns
 */
export function logout() {
    return request({
        url: 'api/logout',
        method: 'POST',
    })
}

/**
 * Lấy csrf cookie
 * @returns
 */
export function getCsrfCookie() {
    return request({
        url: 'http://localhost:9000/sanctum/csrf-cookie',
        method: 'GET',
    })
}



/**
 * Lấy thông tin kì thi, ca thi, phòng thi
 * @returns
 */
export function getUsers() {
    return request({
        url: 'api/users',
        method: 'GET',
    })
}

/**
 * Lấy thông tin kì thi, ca thi, phòng thi
 * @returns
 */
export function saveUser(data) {
    return request({
        url: 'api/users',
        method: 'POST',
        data: data,
    })
}

/**
 * Lấy thông tin kì thi, ca thi, phòng thi
 * @returns
 */
export function updateUser(data) {
    return request({
        url: `api/users/${data.id}`,
        method: 'PUT',
        data: data,
    })
}


/**
 * Lấy thông tin kì thi, ca thi, phòng thi
 * @returns
 */
export function deleteUser(id) {
    return request({
        url: `api/users/${id}`,
        method: 'DELETE',
    })
}

/**
 * Kiểm tra password
 * @returns
 */
export function checkPassword(data) {
    return request({
        url: 'api/user/checkpassword',
        method: 'POST',
        data: data
    })
}

/**
 * Kiểm tra password
 * @returns
 */
export function sendEmailVerify() {
    return request({
        url: 'api/user/sendEmailVerify',
        method: 'POST',
    })
}

/**
 * Kiểm tra password
 * @returns
 */
export function checkEmailExits(data) {
    return request({
        url: 'api/user/checkEmailExits',
        method: 'POST',
        data: data
    })
}



