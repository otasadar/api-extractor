import os, hashlib, urllib2, optparse, base64, cgi, sys, json

url = sys.argv[1]
token = sys.argv[2]

def get_remote_md5_sum(url):

    request = urllib2.Request(url, headers={"Authorization" : "Bearer "+token})
    remote = urllib2.urlopen(request)
    meta = remote.info()
    size = meta.getheaders("Content-Length")[0];
    hash = hashlib.md5()
    total_read = 0

    while True:
        data = remote.read(4096)
        total_read += 4096

        if not data:
            break

        hash.update(data)

    response = {'hash': hash.digest().encode('base64').strip(),'size': size}
    return json.dumps(response)

print(get_remote_md5_sum(url))