(function() {
    if (typeof window === 'undefined' || typeof document === 'undefined') {
        return;
    }

    if (window.fluentBookingGeoInit) {
        return;
    }
    window.fluentBookingGeoInit = true;

    var state = {
        timezone: null,
        country: null,
        dialCode: null
    };

    function dispatchTimezone(tz) {
        if (!tz || tz === state.timezone) {
            return;
        }
        state.timezone = tz;
        window.fluentBookingDetectedTimezone = tz;
        document.dispatchEvent(new CustomEvent('fcal_detected_timezone', {
            detail: {
                timezone: tz
            }
        }));
    }

    function dispatchCountry(country, dialCode) {
        if (!country) {
            return;
        }
        var iso = country.toUpperCase();
        if (state.country === iso) {
            if (dialCode && dialCode !== state.dialCode) {
                state.dialCode = dialCode;
                window.fluentBookingDetectedDialCode = dialCode;
            }
            return;
        }
        state.country = iso;
        window.fluentBookingDetectedCountry = iso;
        if (dialCode) {
            state.dialCode = dialCode;
            window.fluentBookingDetectedDialCode = dialCode;
        }
        document.dispatchEvent(new CustomEvent('fcal_detected_country', {
            detail: {
                country: iso,
                iso2: iso,
                dialCode: dialCode || null
            }
        }));
    }

    function safeTimezone() {
        try {
            var opts = Intl.DateTimeFormat().resolvedOptions();
            return opts && opts.timeZone ? opts.timeZone : null;
        } catch (err) {
            return null;
        }
    }

    function fromLocale(values) {
        if (!values) {
            return null;
        }
        for (var i = 0; i < values.length; i++) {
            var locale = values[i];
            if (!locale || typeof locale !== 'string') {
                continue;
            }
            var parts = locale.replace('@', '-').split(/[-_]/);
            if (parts.length > 1) {
                var candidate = parts[parts.length - 1];
                if (candidate && candidate.length === 2) {
                    return candidate.toUpperCase();
                }
            }
        }
        return null;
    }

    function detectCountryFromLocale() {
        var locales = [];
        try {
            var intlLocale = Intl.DateTimeFormat().resolvedOptions().locale;
            if (intlLocale) {
                locales.push(intlLocale);
            }
        } catch (err) {
            // ignore
        }
        if (Array.isArray(navigator.languages)) {
            locales = locales.concat(navigator.languages);
        }
        if (navigator.language) {
            locales.push(navigator.language);
        }
        return fromLocale(locales);
    }

    function detectFromIp() {
        if (typeof fetch !== 'function') {
            return;
        }
        fetch('https://ipapi.co/json/')
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Failed to fetch geo data');
                }
                return response.json();
            })
            .then(function(data) {
                if (data) {
                    if (data.timezone) {
                        dispatchTimezone(data.timezone);
                    }
                    if (data.country) {
                        dispatchCountry(data.country, data.country_calling_code || null);
                    }
                }
            })
            .catch(function() {
                // ignore network errors silently
            });
    }

    var tz = safeTimezone();
    if (tz) {
        dispatchTimezone(tz);
    }

    var localeCountry = detectCountryFromLocale();
    if (localeCountry) {
        dispatchCountry(localeCountry, null);
    }

    detectFromIp();
})();
