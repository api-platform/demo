import { check, sleep } from "k6";
import http from "k6/http";

export let options = {
    maxRedirects: 0,
    scenarios: {
      default: {
        executor: 'per-vu-iterations',
        vus: 1,
        iterations: 1,
      }
    },
    thresholds: {
        http_req_duration: [{threshold: 'p(95)<5000', abortOnFail: true}], //units in miliseconds 60000ms = 1m
        http_req_failed: [{threshold: 'rate<0.01', abortOnFail: true}], // http errors should be less than 1%
        checks: [{threshold: 'rate>0.95', abortOnFail: true}], // checks must success more than 99%
    },
};

const target=__ENV.TARGET;
console.log(`Running test on ${target}`);

export default function() {
    check_https_redirect();
    check_api_link_header();
    sleep(1);
}

function check_https_redirect() {
    var r = http.get(`http://${target}/`);
    check(r, {
        "http request: status is 301": (r) => r.status === 301,
        "http request: redirection location ok": (r) => r.headers["Location"] === `https://${target}/`,
    });
}

function check_api_link_header() {
    var r = http.get(`https://${target}/books?page=1`);
    check(r, {
        "books API: status is 200": (r) => r.status === 200,
        "books API: Link in https": (r) => r.headers["Link"].match(`https://${target}/docs.jsonld`),
    });
}
