var N3 = require('n3');
var http = require('follow-redirects').http;
var parser = N3.Parser();

// server has an ipv6 address
http.request({
    family: 6,
    port: 3000,
    path: '/parking',
}, (res) => {
    parser.parse(res, consumeTriple)
}).end();

triples = [];

function consumeTriple(error, triple, prefixes) {
    console.log(triple);
}

