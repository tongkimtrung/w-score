/**
 * Sinh mã tự động theo giá trị
 * @param {*} value Giá trị
 * @returns
 */
export function generateCode({value}: { value: any }) {
    var result = '';
    try {
        //xóa khoảng trắng thừa trong chuỗi VD: Đinh     Phú Quý => Đinh Phú QUý
        var stringSplit = String(value).replace(/\s+/g, ' ').trim().split(' ');
        //lấy ra chữ cái đầu tiên từng phần tử
        for (let i = 0; i < stringSplit.length; i++) {
            result += stringSplit[i].toUpperCase().charAt(0).normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[!"#$%&'()*+,-./:;<=>?@[\]^_`{|}~]/g, "");
        }
    } catch (error) {
        console.log(error);
    }
    return result;
}

/**
 * Convert ngày tháng
 * @param {*} tzValue Giá trị cần convert
 * @returns
 */
export function convertTimezoneToDatetime({tzValue}: { tzValue: any }) {
    let tz = new Date(tzValue);
    let y = tz.getFullYear();
    let m = '' + (tz.getMonth() + 1);
    if (m.length < 2) m = '0' + m;
    let d = '' + tz.getDate();
    if (d.length < 2) d = '0' + d;
    let h = '' + tz.getHours();
    if (h.length < 2) h = '0' + h;
    let mi = '' + tz.getMinutes();
    if (mi.length < 2) mi = '0' + mi;
    let s = '00';
    return `${d}/${m}/${y} ${h}:${mi}:${s}`;
}


export function convertTime({string}: { string: any }) {
    var date = new Date(string),
        mnth = ("0" + (date.getMonth() + 1)).slice(-2),
        day = ("0" + date.getDate()).slice(-2),
        hours = ("0" + date.getHours()).slice(-2),
        minutes = ("0" + date.getMinutes()).slice(-2);
    return [hours, minutes].join(":");
}
