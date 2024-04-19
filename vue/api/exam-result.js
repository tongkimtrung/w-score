import request from '../api/utils/request'

export function getExamResultDetail(data) {
    return request({
        url: `api/exam-results/detail`,
        method: 'POST',
        data: data,
    })
}

export function getExamResult(data) {
    return request({
        url: `api/exam-results`,
        method: 'POST',
        data: data,
    })
}
