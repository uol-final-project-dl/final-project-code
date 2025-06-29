import _ = require("lodash");
import axios from 'axios';

declare let window: {
    _: any
    axios: any
}

window._ = _;

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
