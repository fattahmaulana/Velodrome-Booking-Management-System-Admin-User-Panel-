</main></div></div>
<script>
document.getElementById('admin-sidebar-toggle')?.addEventListener('click',()=>{document.getElementById('admin-sidebar').classList.toggle('-translate-x-full')});
document.querySelectorAll('[data-flash]').forEach(el=>{setTimeout(()=>{el.style.transition='all .3s';el.style.opacity='0';setTimeout(()=>el.remove(),300)},4000)});
</script>
</body></html>
