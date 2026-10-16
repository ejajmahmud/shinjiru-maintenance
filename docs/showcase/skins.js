/* Static design previews: authentication belongs on the WordPress server. */
document.querySelectorAll('.gate').forEach(form=>form.addEventListener('submit',event=>{event.preventDefault();form.querySelector('[role="status"]').textContent='Preview only. Connect this form to the plugin’s server-side password handler.';form.querySelector('input').value='';}));
const clock=document.querySelector('[data-demo-countdown]');
if(clock){let end=Date.now()+((2*24+6)*60+24)*60000+18000;function tick(){let total=Math.max(0,Math.floor((end-Date.now())/1000));[86400,3600,60,1].forEach((size,i)=>{clock.querySelectorAll('.num')[i].textContent=String(Math.floor(total/size)).padStart(2,'0');total%=size;});}tick();setInterval(tick,1000);}
