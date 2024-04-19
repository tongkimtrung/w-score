import request from '../api/utils/request'

export function get(data) {
    return request({
        url: 'api/dashboard',
        method: 'GET',
    })
}
