<template>
    <div>
        <div style="width: 100%; margin-bottom: 15px">
            <component v-if="filterComponent != null"
                       :is="filterComponent"
                       v-bind="filterComponentProps"
                       v-model="filterData"
                       v-on:input="fetchTranslations"
            ></component>
        </div>
        <div style="width: 100%; margin-bottom: 15px">
            <label><slot name="header"></slot></label><br>
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
        <div style="width: 100%; margin-bottom: 15px">
            <label>
                <slot name="filter"></slot>
                <input type="text" class="form-control" v-model="filterText">
            </label>
        </div>
        <table class="translations-manager-table">
            <thead>
            <tr style="font-weight: bold; text-transform: uppercase;">
                <th style="max-width: 50%"><slot name="key"></slot></th>
                <th v-for="locale in localesToShow" v-html="locale"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="translation, key in filteredTranslations" :key="key">
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
            defaultLocales: {type: Array, default: []},
            filterComponent: {default: null},
            filterComponentProps: {type: Object, default: () => {return {}}},
        },
        data: function () {
            return {
                localesToShow: [],
                translations: [],
                labels: [],
                dirties: {},
                filterData: {},
                filterText: '',
            }
        },
        mounted() {
            this.localesToShow = this.defaultLocales;
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
                    locales: this.locales,
                    filterData: this.filterData
                }).then((response) => {
                    this.translations = response.data.translations;
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
        computed: {
            filteredTranslations: function() {
                if (this.filterText.trim() == '') {
                    return this.translations;
                }
                let result = {};
                let included = false;
                for (let i in this.translations) {
                    included = false;
                    if (this.translations.hasOwnProperty(i)) {
                        if (i.toUpperCase().includes(this.filterText.toUpperCase())) {
                            result[i] = this.translations[i];
                            included = true;
                        }
                        if (!included) {
                            for (let l in this.localesToShow) {
                                if (this.localesToShow.hasOwnProperty(l)) {
                                    if (typeof(this.translations[i][this.localesToShow[l]]) != 'undefined') {
                                        if (this.translations[i][this.localesToShow[l]].toUpperCase().includes(this.filterText.toUpperCase())) {
                                            result[i] = this.translations[i];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                return result;
            }
        },
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