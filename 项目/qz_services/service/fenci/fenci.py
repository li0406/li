# encoding=utf-8
#!  /usr/bin env python3

import jieba
import json
import re
from flask import Flask,request

app = Flask(__name__)
jieba.set_dictionary('dict/dict.txt')
USAGE = "usage: The lack of key words 'word'"

@app.route('/', methods=['GET','POST'])
def words():
    if request.method == "POST":        
        words = request.form.get('word')
        if words is None:
            return  USAGE
			
        words = words.strip()

        words =  re.split(' |，|,|\n',words)
        seg_list = []
        for word in words:
            a = jieba.cut_for_search(word)
            seg_list.extend(a)

        return json.dumps(seg_list,ensure_ascii=False)
    return "usage: request must be post"

if __name__ == '__main__':
    app.run(host='0.0.0.0',port=11001,threaded=True)
