from django import forms
class InputTool(forms.Form):
    web = forms.CharField(label="Nhập đường link trang truyện:", max_length=1000)