import request from '../api/utils/request'

/**
 * Thêm mới kì thi
 * @returns
 */
export function saveExamManager(data) {
    return request({
        url: 'api/exams',
        method: 'POST',
        data: data,
    })
}

/**
 * Thêm mới kì thi
 * @returns
 */
export function updateExamManager(data) {
    return request({
        url: 'api/exams',
        method: 'PUT',
        data: data,
    })
}

/**
 * Thêm mới kì thi
 * @returns
 */
export function deleteExamManager(id) {
    return request({
        url: `api/exams/${id}`,
        method: 'DELETE',
    })
}

/**
 * Thêm mới kì thi
 * @returns
 */
export function getExamManager() {
    return request({
        url: 'api/exams',
        method: 'GET',
    })
}

export function checkExamShiftCodeExits(data) {
    return request({
        url: 'api/exam-shifts/checkCodeExits',
        method: 'POST',
        data: data,
    })
}


/**
 * Cập nhật ca thi
 * @param {*} data
 * @returns
 */
export function updateExamShift(data) {
    return request({
        url: 'api/exam-shifts',
        method: 'PUT',
        data: data,
    })
}

/**
 * Lấy thông tin ca thi
 * @param {*} data
 * @returns
 */
export function getExamShifts(data) {
    return request({
        url: 'api/exams/getExamShifts',
        method: 'POST',
        data: data,
    })
}

/**
 * Lấy thông tin ca thi
 * @param {*} data
 * @returns
 */
export function deleteExamShift(data) {
    return request({
        url: 'api/exam-shifts',
        method: 'DELETE',
        data: data,
    })
}

/**
 * Thêm mới ca thi
 * @param {*} data
 * @returns
 */
export function insertExamShift(data) {
    return request({
        url: 'api/exam-shifts',
        method: 'POST',
        data: data,
    })
}
