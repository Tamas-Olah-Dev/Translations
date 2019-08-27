<template>
    <div>
        <div style="width: 100%">
            <h4><slot name="header"></slot></h4>
            <label v-for="locale in locales"
                   :key="locale+'-checkbox'"
                   :for="locale+'-checkbox'"
                   style="font-weight: bold; margin-right: 10px; text-transform: uppercase;"
            >
                <input type="checkbox"
                       v-model="localesToShow"
                       :id="locale+'-checkbox'"
                       :value="locale"
                >
                {{ locale }}
            </label>
        </div>
        <table class="translations-manager-table">
            <thead>
            <tr style="font-weight: bold; text-transform: uppercase;">
                <td><slot name="key"></slot></td>
                <td v-for="locale in localesToShow" v-html="locale"></td>
            </tr>
            </thead>
            <tbody>
            <tr v-for="translation, key in translations" :key="key">
                <td v-html="key"></td>
                <td v-for="locale in localesToShow">
                    <input type="text"
                           class="form-control"
                           v-model="translations[key][locale]"
                           v-bind:class="{'dirty-input': isDirty(key, locale)}"
                           v-on:input="setDirty(key, locale)"
                           v-on:blur="storeTranslation(key, locale)"
                    >
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        props: {
            locales: {type: Array},
            operationsUrl: {type: String},
            keyProperty: {type: String, default: 'id'},
            translationProperty: {type: String, default: 'translation'},
        },
        data: function () {
            return {
                localesToShow: [],
                translations: [],
                dirties: {}
            }
        },
        mounted() {
            this.fetchTranslations();
        },
        methods: {
            setDirty: function(key, locale) {
                if (typeof(this.dirties[key]) == 'undefined') {
                    Vue.set(this.dirties, key, {});
                }
                Vue.set(this.dirties[key], locale, 1);
            },
            clearDirty: function(key, locale) {
                if (this.isDirty(key, locale)) {
                    Vue.delete(this.dirties[key], locale);
                }
            },
            isDirty: function(key, locale) {
                return typeof(this.dirties[key]) != 'undefined'
                    && typeof(this.dirties[key][locale]) != 'undefined'
            },
            fetchTranslations: function() {
                window.axios.post(this.operationsUrl, {
                    action: 'fetchTranslations',
                    locales: this.locales
                })
                    .then((response) => {
                        this.translations = response.data;
                    });
            },
            storeTranslation: function(key, locale) {
                window.axios.post(this.operationsUrl, {
                    action: 'storeTranslation',
                    key: key,
                    locale: locale,
                    translation: this.translations[key][locale]
                }).then((response) => {
                    this.clearDirty(key, locale);
                });
            }

        },
        computed: {},
        watch: {}

    }
</script>
<style>
    .translations-manager-table {
        width: 100%;
    }
    .translations-manager-table td,
    .translations-manager-table th {
        border: 1px solid lightgrey
    }
    .dirty-input {
        color: blue !important;
    }
</style>