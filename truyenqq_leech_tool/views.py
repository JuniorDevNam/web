from django.shortcuts import render
from django.http import HttpResponse
from .forms import InputTool
from . import tool
import random
# Create your views here.
user_agent_list = [
   "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36",
   "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36",
   "Mozilla/5.0 (iPad; CPU OS 15_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/104.0.5112.99 Mobile/15E148 Safari/604.1",
   "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.3",
   "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0",
   "Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Mobile Safari/537.3"
]
def Tool(request):
    if request.method == "POST":
        form = InputTool(request.POST)
        if form.is_valid():
            web = form.cleaned_data['web']
            referer = f'https://{web.split("/")[2]}/'
            domain = f'https://{web.split("/")[2]}'
            headers = {
            'Connection': 'keep-alive',
            'Cache-Control': 'max-age=0',
            'Upgrade-Insecure-Requests': '1',
            'User-Agent': random.choice(user_agent_list),
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Encoding': 'gzip, deflate',
            'Accept-Language': 'en-US,en;q=0.9,fr;q=0.8',
            'referer': referer
                        }
            print("Server:",referer)
            if "chap" in web:
                print("Có vẻ đây là link của 1 chap đơn. Tiến hành tải...")
                tool.onechapter(web, headers)
            else:
                print("Có vẻ như đây là đường link của cả một truyện. Tiến hành tải tất cả chương mà truyện hiện có...")
                tool.allchapters(web, headers, domain)
    else:
        form = InputTool()
    
    return render(request, 'index.html',{'form': form})