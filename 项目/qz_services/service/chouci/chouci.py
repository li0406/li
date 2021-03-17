#!  /usr/bin env python3
# encoding=utf-8
import sys
sys.path.append('../')

import jieba
import jieba.analyse
import json
from flask import Flask,request
from optparse import OptionParser

app = Flask(__name__)

USAGE = "usage:    python extract_tags.py [file name] -k [top k]"

@app.route('/', methods=['GET','POST'])
def worlds():
        if request.method == 'POST':
                topK  = request.form.get('limit')
                if topK is None:
                        topK =  ''

                if len(topK) == 0:
                        topK = 10
                else:
                        topK = int(topK)

                content  = request.form.get('content')
                tags = jieba.analyse.extract_tags(content, topK=topK)

                return json.dumps(tags,ensure_ascii=False)

        return "usage: request must be post"
if __name__ == '__main__':
    app.run(host='0.0.0.0',port=11000,threaded=True)
