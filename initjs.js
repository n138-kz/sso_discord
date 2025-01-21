function init_setTopMaker() {
    window.addEventListener('DOMContentLoaded', (e)=>{
        const html_top = document.createElement('a');
        html_top.name='_';
        document.querySelector('body').prepend(html_top);
    }, false);
}
function init_setFavicon() {
    window.addEventListener('DOMContentLoaded', (e)=>{
        let favicon;
        ['icon', 'apple-touch-icon', 'favicon'].forEach((e)=>{
            favicon = document.createElement('link');
            favicon.rel = e;
            favicon.href = 'https://www.google.com/s2/favicons?size=64&domain=https://google.com/';
            document.querySelectorAll('head').forEach((e)=>e.prepend(favicon));
        });
    }, false);
}
function init_setHeaderText(text={text:null,href:null}) {
    if (text===null || text.text===null || text.href===null) {
        text={text:document.title,href:location.origin};
    }
    window.addEventListener('DOMContentLoaded', (e)=>{
        let header1 = document.createElement('h1');
        let header1_link = document.createElement('a');
        header1_link.href = text.href;
        header1_link.setAttribute('onclick', 'sessionStorage.clear();localStorage.clear();')
        header1_link.title = text.text;
        let header1_text = document.createTextNode(text.text);
        header1_link.appendChild(header1_text);
        header1.appendChild(header1_link);
        document.querySelectorAll('body').forEach((e)=>e.prepend(header1));
    }, false);
}
