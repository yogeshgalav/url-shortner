import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
// Axios v1.6+ will automatically send X-XSRF-TOKEN
// when withCredentials is true and the XSRF cookie name matches.
window.axios.defaults.withXSRFToken = true;
