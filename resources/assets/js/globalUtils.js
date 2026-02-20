(function(global) {
    global.isEmpty = function(value) {
        if (
            value === null ||
            value === undefined ||
            // value === false ||
            value === 0 ||
            value === "" ||
            (typeof value === 'string' && value.trim() === "") ||
            (typeof value === 'number' && isNaN(value)) ||
            (Array.isArray(value) && value.length === 0) ||
            (typeof value === 'object' && value.constructor === Object && Object.keys(value).length === 0)
        ) {
            return true;
        }
        return false;
    };
})(typeof window !== 'undefined' ? window : global);

export function isEmpty(value) {
    if (
        value === null ||
        value === undefined ||
        // value === false ||
        value === 0 ||
        value === "" ||
        (typeof value === 'string' && value.trim() === "") ||
        (typeof value === 'number' && isNaN(value)) ||
        (Array.isArray(value) && value.length === 0) ||
        (typeof value === 'object' && value.constructor === Object && Object.keys(value).length === 0)
    ) {
        return true;
    }
    return false;
}

(function(global) {
    global.shiftValue = function (arr, value) {
        const index = arr.indexOf(value);
        if (index !== -1) {
            return arr.splice(index, 1)[0];
        }
        return undefined;
    }

})(typeof window !== 'undefined' ? window : global);

export function shiftValue(arr, value) {
    const index = arr.indexOf(value);
    if (index !== -1) {
        return arr.splice(index, 1)[0];
    }
    return undefined;
}

(function(global) {
    global.capitalizeFirstLowercaseRest = function(value) {
        return (value ?? '')
        .toLowerCase()
        .replace(/^./, char => char.toUpperCase());
    };
})(typeof window !== 'undefined' ? window : global);

export function capitalizeFirstLowercaseRest(value) {
    return (value ?? '')
        .toLowerCase()
        .replace(/^./, char => char.toUpperCase());
}

(function(global) {
    global.getBrowser = function(value) {
        const userAgent = navigator.userAgent;
        let browserName = 'Unknown';

        if (userAgent.indexOf('Edg') > -1) {
            browserName = 'Edge';
        } else if (userAgent.indexOf('OPR') > -1) {
            browserName = 'Opera';
        } else if (userAgent.indexOf('Chrome') > -1) {
            browserName = 'Chrome';
        } else if (userAgent.indexOf('Safari') > -1) {
            browserName = 'Safari';
        } else if (userAgent.indexOf('Firefox') > -1) {
            browserName = 'Firefox';
        } else if (userAgent.indexOf('MSIE') > -1 || !!document.documentMode === true) {
            browserName = 'IE';
        }

        return browserName;
    };
})(typeof window !== 'undefined' ? window : global);

export function getBrowser() {
    const userAgent = navigator.userAgent;
    let browserName = 'Unknown';

    if (userAgent.indexOf('Edg') > -1) {
        browserName = 'Edge'; // still Chromium
    } else if (userAgent.indexOf('OPR') > -1) {
        browserName = 'Opera';
    } else if (userAgent.indexOf('Chrome') > -1) {
        browserName = 'Chrome';
    } else if (userAgent.indexOf('Safari') > -1) {
        browserName = 'Safari';
    } else if (userAgent.indexOf('Firefox') > -1) {
        browserName = 'Firefox';
    } else if (userAgent.indexOf('MSIE') > -1 || !!document.documentMode === true) {
        browserName = 'IE';
    }

    return browserName;
}