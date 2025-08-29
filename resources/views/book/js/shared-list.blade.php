<script>
(function(){
    var selectPageTable = document.getElementById('page-select-card');
    var pageNumTalbe = 1;

    function buildCardHtml(el){
        var color = (el.type != 1) ? 'warning' : 'info';
        var head = el.inputSubject || '';
        var from = el.selectBookFrom || '';
        var time = el.showTime || '';
        var onclick = "openPdf('" + (el.url||'') + "','" + el.id + "','" + (el.status||'') + "','" + (el.type||'') + "','" + (el.is_number_stamp||'') + "','" + (el.inputBookregistNumber||'') + "','" + (el.position_id||'') + "')";
        return '<a href="javascript:void(0)" onclick="'+onclick+'">'
            + '<div class="card border-'+color+' mb-2">'
            +   '<div class="card-header text-dark fw-bold">'+head+'</div>'
            +   '<div class="card-body text-dark">'
            +     '<div class="row">'
            +       '<div class="col-9">'+from+'</div>'
            +       '<div class="col-3 fw-bold">'+time+' น.</div>'
            +     '</div>'
            +   '</div>'
            + '</div>'
            +'</a>';
    }

    function refreshCards(list){
        var box = document.getElementById('box-card-item');
        var viewer = document.getElementById('div-canvas');
        if (!box) return;
        box.innerHTML='';
        if (viewer) viewer.innerHTML = '<div style="position: relative;"><canvas id="pdf-render"></canvas><canvas id="mark-layer" style="position: absolute; left: 0; top: 0;"></canvas></div>';
        (list||[]).forEach(function(el){ box.insertAdjacentHTML('beforeend', buildCardHtml(el)); });
    }

    function ajaxTable(pages){
        $.ajax({
            type: 'post', url: '/book/dataList', data: { pages: pages }, dataType: 'json',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(resp){ if(resp && resp.status){ refreshCards(resp.book); } }
        });
    }
    window.ajaxTable = ajaxTable; // expose if needed

    if (selectPageTable){
        selectPageTable.addEventListener('change', function(){
            var v = parseInt(this.value||'1'); pageNumTalbe = v; ajaxTable(v);
        });
    }
    var nextBtn = document.getElementById('nextPage');
    var prevBtn = document.getElementById('prevPage');
    if (nextBtn) nextBtn.addEventListener('click', function(){ pageNumTalbe++; if(selectPageTable) selectPageTable.value = pageNumTalbe; ajaxTable(pageNumTalbe); });
    if (prevBtn) prevBtn.addEventListener('click', function(){ pageNumTalbe = Math.max(1, pageNumTalbe-1); if(selectPageTable) selectPageTable.value = pageNumTalbe; ajaxTable(pageNumTalbe); });

    var searchBtn = document.getElementById('search_btn');
    var searchInput = document.getElementById('inputSearch');
    function doSearch(){
        var q = (searchInput && searchInput.value) ? searchInput.value : '';
        $.ajax({
            type: 'post', url: '/book/dataListSearch', data: { pages: 1, search: q }, dataType: 'json',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(resp){
                if(!resp || !resp.status) return;
                refreshCards(resp.book);
                pageNumTalbe = 1;
                var select = document.getElementById('page-select-card');
                if (select){
                    select.innerHTML='';
                    var total = resp.totalPages || 1;
                    for (var i=1;i<=total;i++){ select.insertAdjacentHTML('beforeend','<option value="'+i+'">'+i+'</option>'); }
                }
            }
        });
    }
    if (searchBtn) searchBtn.addEventListener('click', function(e){ e.preventDefault(); doSearch(); });
    if (searchInput) searchInput.addEventListener('keydown', function(e){ if (e.key==='Enter'){ e.preventDefault(); doSearch(); }});
})();
</script>
