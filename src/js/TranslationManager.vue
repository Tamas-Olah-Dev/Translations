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
        <div v-if="loading" v-html="spinnerSrc"></div>
        <table v-else class="translations-manager-table">
            <thead>
            <tr style="font-weight: bold; text-transform: uppercase;">
                <th style="max-width: 50%"><slot name="key"></slot></th>
                <th v-for="locale in localesToShow" v-html="locale"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="translation, key in filteredTranslations" :key="key" :class="tableRowClass(key)">
                <td v-html="key"></td>
                <td v-for="locale in localesToShow">
                    <span v-if="readonlyLocales.includes(locale)"
                          v-html="translations[key][locale]"
                    ></span>
                    <input v-else
                           type="text"
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
            spinnerSrc: {type: String, default: '<img style="max-height: 1em" alt="" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPgo8c3ZnIHdpZHRoPSI0MHB4IiBoZWlnaHQ9IjQwcHgiIHZpZXdCb3g9IjAgMCA0MCA0MCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWw6c3BhY2U9InByZXNlcnZlIiBzdHlsZT0iZmlsbC1ydWxlOmV2ZW5vZGQ7Y2xpcC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlLWxpbmVqb2luOnJvdW5kO3N0cm9rZS1taXRlcmxpbWl0OjEuNDE0MjE7IiB4PSIwcHgiIHk9IjBweCI+CiAgICA8ZGVmcz4KICAgICAgICA8c3R5bGUgdHlwZT0idGV4dC9jc3MiPjwhW0NEQVRBWwogICAgICAgICAgICBALXdlYmtpdC1rZXlmcmFtZXMgc3BpbiB7CiAgICAgICAgICAgICAgZnJvbSB7CiAgICAgICAgICAgICAgICAtd2Via2l0LXRyYW5zZm9ybTogcm90YXRlKDBkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICAgIHRvIHsKICAgICAgICAgICAgICAgIC13ZWJraXQtdHJhbnNmb3JtOiByb3RhdGUoLTM1OWRlZykKICAgICAgICAgICAgICB9CiAgICAgICAgICAgIH0KICAgICAgICAgICAgQGtleWZyYW1lcyBzcGluIHsKICAgICAgICAgICAgICBmcm9tIHsKICAgICAgICAgICAgICAgIHRyYW5zZm9ybTogcm90YXRlKDBkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICAgIHRvIHsKICAgICAgICAgICAgICAgIHRyYW5zZm9ybTogcm90YXRlKC0zNTlkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICB9CiAgICAgICAgICAgIHN2ZyB7CiAgICAgICAgICAgICAgICAtd2Via2l0LXRyYW5zZm9ybS1vcmlnaW46IDUwJSA1MCU7CiAgICAgICAgICAgICAgICAtd2Via2l0LWFuaW1hdGlvbjogc3BpbiAxLjVzIGxpbmVhciBpbmZpbml0ZTsKICAgICAgICAgICAgICAgIC13ZWJraXQtYmFja2ZhY2UtdmlzaWJpbGl0eTogaGlkZGVuOwogICAgICAgICAgICAgICAgYW5pbWF0aW9uOiBzcGluIDEuNXMgbGluZWFyIGluZmluaXRlOwogICAgICAgICAgICB9CiAgICAgICAgXV0+PC9zdHlsZT4KICAgIDwvZGVmcz4KICAgIDxnIGlkPSJvdXRlciI+CiAgICAgICAgPGc+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMCwwQzIyLjIwNTgsMCAyMy45OTM5LDEuNzg4MTMgMjMuOTkzOSwzLjk5MzlDMjMuOTkzOSw2LjE5OTY4IDIyLjIwNTgsNy45ODc4MSAyMCw3Ljk4NzgxQzE3Ljc5NDIsNy45ODc4MSAxNi4wMDYxLDYuMTk5NjggMTYuMDA2MSwzLjk5MzlDMTYuMDA2MSwxLjc4ODEzIDE3Ljc5NDIsMCAyMCwwWiIgc3R5bGU9ImZpbGw6YmxhY2s7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNNS44NTc4Niw1Ljg1Nzg2QzcuNDE3NTgsNC4yOTgxNSA5Ljk0NjM4LDQuMjk4MTUgMTEuNTA2MSw1Ljg1Nzg2QzEzLjA2NTgsNy40MTc1OCAxMy4wNjU4LDkuOTQ2MzggMTEuNTA2MSwxMS41MDYxQzkuOTQ2MzgsMTMuMDY1OCA3LjQxNzU4LDEzLjA2NTggNS44NTc4NiwxMS41MDYxQzQuMjk4MTUsOS45NDYzOCA0LjI5ODE1LDcuNDE3NTggNS44NTc4Niw1Ljg1Nzg2WiIgc3R5bGU9ImZpbGw6cmdiKDIxMCwyMTAsMjEwKTsiLz4KICAgICAgICA8L2c+CiAgICAgICAgPGc+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMCwzMi4wMTIyQzIyLjIwNTgsMzIuMDEyMiAyMy45OTM5LDMzLjgwMDMgMjMuOTkzOSwzNi4wMDYxQzIzLjk5MzksMzguMjExOSAyMi4yMDU4LDQwIDIwLDQwQzE3Ljc5NDIsNDAgMTYuMDA2MSwzOC4yMTE5IDE2LjAwNjEsMzYuMDA2MUMxNi4wMDYxLDMzLjgwMDMgMTcuNzk0MiwzMi4wMTIyIDIwLDMyLjAxMjJaIiBzdHlsZT0iZmlsbDpyZ2IoMTMwLDEzMCwxMzApOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTI4LjQ5MzksMjguNDkzOUMzMC4wNTM2LDI2LjkzNDIgMzIuNTgyNCwyNi45MzQyIDM0LjE0MjEsMjguNDkzOUMzNS43MDE5LDMwLjA1MzYgMzUuNzAxOSwzMi41ODI0IDM0LjE0MjEsMzQuMTQyMUMzMi41ODI0LDM1LjcwMTkgMzAuMDUzNiwzNS43MDE5IDI4LjQ5MzksMzQuMTQyMUMyNi45MzQyLDMyLjU4MjQgMjYuOTM0MiwzMC4wNTM2IDI4LjQ5MzksMjguNDkzOVoiIHN0eWxlPSJmaWxsOnJnYigxMDEsMTAxLDEwMSk7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNMy45OTM5LDE2LjAwNjFDNi4xOTk2OCwxNi4wMDYxIDcuOTg3ODEsMTcuNzk0MiA3Ljk4NzgxLDIwQzcuOTg3ODEsMjIuMjA1OCA2LjE5OTY4LDIzLjk5MzkgMy45OTM5LDIzLjk5MzlDMS43ODgxMywyMy45OTM5IDAsMjIuMjA1OCAwLDIwQzAsMTcuNzk0MiAxLjc4ODEzLDE2LjAwNjEgMy45OTM5LDE2LjAwNjFaIiBzdHlsZT0iZmlsbDpyZ2IoMTg3LDE4NywxODcpOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTUuODU3ODYsMjguNDkzOUM3LjQxNzU4LDI2LjkzNDIgOS45NDYzOCwyNi45MzQyIDExLjUwNjEsMjguNDkzOUMxMy4wNjU4LDMwLjA1MzYgMTMuMDY1OCwzMi41ODI0IDExLjUwNjEsMzQuMTQyMUM5Ljk0NjM4LDM1LjcwMTkgNy40MTc1OCwzNS43MDE5IDUuODU3ODYsMzQuMTQyMUM0LjI5ODE1LDMyLjU4MjQgNC4yOTgxNSwzMC4wNTM2IDUuODU3ODYsMjguNDkzOVoiIHN0eWxlPSJmaWxsOnJnYigxNjQsMTY0LDE2NCk7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNMzYuMDA2MSwxNi4wMDYxQzM4LjIxMTksMTYuMDA2MSA0MCwxNy43OTQyIDQwLDIwQzQwLDIyLjIwNTggMzguMjExOSwyMy45OTM5IDM2LjAwNjEsMjMuOTkzOUMzMy44MDAzLDIzLjk5MzkgMzIuMDEyMiwyMi4yMDU4IDMyLjAxMjIsMjBDMzIuMDEyMiwxNy43OTQyIDMzLjgwMDMsMTYuMDA2MSAzNi4wMDYxLDE2LjAwNjFaIiBzdHlsZT0iZmlsbDpyZ2IoNzQsNzQsNzQpOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTI4LjQ5MzksNS44NTc4NkMzMC4wNTM2LDQuMjk4MTUgMzIuNTgyNCw0LjI5ODE1IDM0LjE0MjEsNS44NTc4NkMzNS43MDE5LDcuNDE3NTggMzUuNzAxOSw5Ljk0NjM4IDM0LjE0MjEsMTEuNTA2MUMzMi41ODI0LDEzLjA2NTggMzAuMDUzNiwxMy4wNjU4IDI4LjQ5MzksMTEuNTA2MUMyNi45MzQyLDkuOTQ2MzggMjYuOTM0Miw3LjQxNzU4IDI4LjQ5MzksNS44NTc4NloiIHN0eWxlPSJmaWxsOnJnYig1MCw1MCw1MCk7Ii8+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4K" />'}
        },
        data: function () {
            return {
                localesToShow: [],
                translations: [],
                labels: [],
                dirties: {},
                filterData: {},
                filterText: '',
                readonlyLocales: [],
                loading: true,
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
                this.loading = true;
                window.axios.post(this.operationsUrl, {
                    action: 'fetchTranslations',
                    locales: this.locales,
                    filterData: this.filterData
                }).then((response) => {
                    this.translations = response.data.translations;
                    this.readonlyLocales = response.data.readonlyLocales;
                    this.loading = false;
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
            },
            tableRowClass: function(key) {
                for (let i = 0; i < this.localesToShow.length; i++) {
                    if ((typeof(this.translations[key][this.localesToShow[i]]) == 'undefined')
                        || (this.translations[key][this.localesToShow[i]] == '')) {
                        return 'translation-row-incomplete';
                    }
                }
                return '';
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
    .translation-row-incomplete {
        background-color: #fae9eb;
    }
</style>
