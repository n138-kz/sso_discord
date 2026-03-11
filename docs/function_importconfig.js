function importconfig(jsontext, provider='', item=undefined) {
	item_wrapper=item;
	try {
		item=item.parentNode.closest('td').querySelector('span');
	} catch (error) { item=undefined; }
	console.debug(`[importconfig]`, jsontext, provider, item, item_wrapper);

	if (item!==undefined) {
		item.style.color='#0a0';
		item.style.display='block';
		item.style.removeProperty('width');
		item.innerText='';
	}

	try {
		params=JSON.parse(jsontext);
		console.debug(params);

		['client_id','client_secret','redirect_uri','scope'].map(e=>{
			if(params[e]===undefined||params[e]===null||params[e]===''){
				throw new Error(`${e} has not in value.`);
			}
		})

		localStorage.setItem( (btoa(location.href)).slice(0, 16) + `.${provider}.client_id`, params['client_id'] );
		localStorage.setItem( (btoa(location.href)).slice(0, 16) + `.${provider}.client_secret`, params['client_secret'] );
		localStorage.setItem( (btoa(location.href)).slice(0, 16) + `.${provider}.redirect_uri`, params['redirect_uri'] );
		localStorage.setItem( (btoa(location.href)).slice(0, 16) + `.${provider}.scope`, params['scope'] );
		
		if (item!==undefined) {
			item.innerText=`保存しました`;
		}

		setTimeout(()=>location.replace(`${location.origin}${location.pathname}`), 1000);

	} catch (error) {
		console.error(error);
		if (item!==undefined) {
			item.style.color='#f00';
			item.style.display='block';
			item.innerText=`${error}`;
		}
	}
}
