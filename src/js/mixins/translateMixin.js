export const translateMixin = {
    methods: {
        translate: function(string, replacements) {
            if (typeof(replacements) == 'undefined') {
                replacements = {};
            }
            if (typeof(window.laravelTranslations) == 'undefined') {
                window.laravelTranslations = {};
            }
            return this.makeReplacements(string, replacements);
            if (typeof(window.laravelLocales[window.laravelLocale][string]) != 'undefined') {
                return window.laravelLocales[window.laravelLocale][string];
            }
            if (typeof(this.$root.translate) != 'undefined') {
                return this.$root.translate(string);
            }

            return string;
        },

        makeReplacements: function(string, replacements) {
            let result = typeof(window.laravelTranslations[string]) == 'undefined'
                ? string
                : window.laravelTranslations[string];
            for (let i in replacements) {
                if (replacements.hasOwnProperty(i)) {
                    result = result.replace(':'+i, replacements[i]);
                }
            }
            return result;
        }
    }
}