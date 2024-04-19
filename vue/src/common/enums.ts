// enums.js
export const FORM_MODE = {
    NONE: 0,
    INSERT: 1,
    UPDATE: 2,
    DELETE: 3,
    DUPLICATE: 4
};

export const KEY_CODE = {
    ENTER: 13,
    COMMA: 188,
    BACK_SPACE: 8,
    TAB: 9,
    SHIFT: 16,
    CTRL: 17,
    ESC: 27,
    ALT: 18,
    ARROW_DOWN: 40,
    ARROW_UP: 38
};

export const FILE_TYPE = {
    XLSX: 0,
    ZIP: 1,
    LIST: 2, // file danh sách
    EXAM: 3, // file bài thi
};

export const RESPONSE_STATUS = {
    HTTP_OK: 200,
    HTTP_UNAUTHORIZED: 401,
    HTTP_UNPROCESSABLE_ENTITY: 422,
    HTTP_METHOD_NOT_ALLOWED: 405,
    HTTP_INTERNAL_SERVER_ERROR: 500,
};

export const MESSAGE = {
    HTTP_INSERT_OK: 'Thêm mới thành công',
    HTTP_UPDATE_OK: 'Cập nhật thành công',
    HTTP_DELETE_OK: 'Xóa thành công',
    DROPDOWN_PLACEHOLDER_SETUP: 'Vui lòng chọn 1 hạng mục',
    INPUT_NUMBER_PLACEHOLDER_SETUP: 'Nhập điểm số',
    INPUT_PLACEHOLDER_SETUP: 'Nhập nội dung',
    INVALID_POINT_SETUP: 'Điểm không được để trống',
    INVALID_CONTENT_SETUP: 'Nội dung không được để trống',
    INVALID_CONTENT_CHILD_SETUP: 'Vui lòng thiết lập ít nhất 1 tiêu chí',
};
