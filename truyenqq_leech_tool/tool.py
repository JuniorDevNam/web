import requests
from bs4 import BeautifulSoup
from os.path import join
from os import makedirs
import sys
import time
import re

#debug_html = join(sys.path[0],"debug.html")
#debug_html_ch = join(sys.path[0],"debug_ch.html")

#https://stackoverflow.com/questions/14587728/what-does-this-error-in-beautiful-soup-means



def onechapter(web, headers):
    res = requests.get(web,headers=headers)
    html_content = res.text
    soup = BeautifulSoup(html_content, 'html.parser')
    #debug
    #with open(debug_html_ch, 'w', encoding='utf8') as f:
    #    f.write(str(soup))
    # Tìm thẻ h1 có class
    h1_tag = soup.find("h1", class_="detail-title txt-primary")

    # Trích xuất văn bản từ thẻ h1
    if h1_tag:
        #h1_text = h1_tag.text.replace("\n","").strip()
        #h1_text = re.sub(r'\s+', ' ', h1_text).strip()
        h1_text = re.sub(r'\s+', ' ', h1_tag.text).strip()
        print(h1_text)
    img_links = []
    for x in soup.find_all("div", class_="page-chapter"):#, id="image"):
        for y in x.find_all("img"):
            img_links.append(y.get("data-original"))
    #debug
    print(img_links)
    #parts = web.split("/")
    #title_tag = soup.find('title')
    #title = title_tag.string.replace(":"," -")
    #title = web.split("/")[-1]
    folder = join(sys.path[0],"downloads",h1_text)
    makedirs(folder, exist_ok=True)
    for index, link in enumerate(img_links):
        print(link)
        file = join(folder,f"image_{index}.jpg")
        response = requests.get(link, headers=headers)
        with open(file, "wb") as f:
            f.write(response.content)
    time.sleep(1)
    print("Xong.")

def allchapters(web, headers, domain):
    res = requests.get(web,headers=headers)
    html_content = res.text
    soup = BeautifulSoup(html_content, 'html.parser')
    chapters = []
    for x in soup.find_all("div", class_="works-chapter-item"):
        for y in x.find_all("a"):
            chapters.append(f'{domain}{y.get("href")}')
    chapters = chapters[::-1]
    print(chapters)
    #title_tag = soup.find("title")
    #title = title_tag.string
    h1_tag = soup.find("h1", attrs={'itemprop': 'name'})
    # Trích xuất văn bản từ thẻ h1
    if h1_tag:
        #h1_text = h1_tag.text.replace("\n","").strip()
        #h1_text = re.sub(r'\s+', ' ', h1_text).strip()
        title = re.sub(r'\s+', ' ', h1_tag.text).strip()
        print(title)
    for link in chapters:
        onechapter(link, headers)
