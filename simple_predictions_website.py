import os
import sys
import requests
import torch
from argparse import ArgumentParser
from newspaper import Article

sys.path.insert(0, os.path.abspath("./src"))
from extractive import ExtractiveSummarizer

parser = ArgumentParser(description='Indonesian News Summarization')
parser.add_argument('--model', type=str, default='./models/bert-base-multilingual-uncased_sigmoid_none_use_token_type_ids_1_sent_rep_tokens_5e-05_0.1_8_4_transformer_2_fixed.ckpt',help='trained model')
parser.add_argument('--source',type=str, default='./kumpulan_berita/berita_1_detik.txt', help='source of news')
parser.add_argument('--percentages',type=float, default='20.0',help='percentages of summary sentences')
parser.add_argument('--save_dir', type=str, default='./kumpulan_berita', help='directory of saved news article')

args = parser.parse_args()

# Load model 
checkpoint = torch.load(args.model, map_location=torch.device('cpu'))
model = ExtractiveSummarizer(checkpoint['hyperparameters'])
model.load_state_dict(checkpoint['model_state_dict'])

# Check source
try:
    #Get news article
    assert(requests.get(args.source))
    news_article = Article(args.source)
    news_article.download()
    news_article.parse()
    temp = news_article.text.split('\n\n')
    contents = [sent+"\n" for sent in temp]
    file_name = news_article.title.replace(' ','-') + '.txt'
    source = 'url/' + file_name
    with open(os.path.join(args.save_dir, file_name), 'w', encoding='utf-8') as f:
        f.write('\n'.join(contents))
except:	
    # Open text file
    with open(args.source) as f:
        contents = f.readlines()
    source = 'file/' + os.path.basename(args.source)
 
# Convert percentages to number of sentences (based on number of sentences in news text)
num_sentences = int((args.percentages*0.01) * len(contents))

# Check if sentences less than 1, then set number of sentence to min. 1
if num_sentences < 1:
	num_sentences = 1
    
# Predict
output = model.predict(contents, source, num_sentences)
print(output)
