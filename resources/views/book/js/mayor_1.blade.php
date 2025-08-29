@section('script')
<?php $position = [1 => 'สำนักงานปลัด', 2 => 'งานกิจการสภา', 3 => 'กองคลัง', 4 => 'กองช่าง', 5 => 'กองการศึกษา ศาสนาและวัฒนธรรม', 6 => 'ฝ่ายศูนย์รับเรื่องร้องเรียน-ร้องทุกข์', 7 => 'ฝ่ายเลือกตั้ง', 8 => 'ฝ่ายสปสช.', 9 => 'ศูนย์ข้อมูลข่าวสาร']; ?>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $('.btn-default').hide();
    var signature = '{{$signature}}';
    var selectPageTable = document.getElementById('page-select-card');
    var pageTotal = '{{$totalPages}}';
    var pageNumTalbe = 1;

    var imgData = null;
    // Keep last opened doc to refresh preview after save
    var lastOpen = null;
    // Preload signature image and track load state
    var signatureImg = new Image();
    var signatureImgLoaded = false;
    signatureImg.onload = function(){ signatureImgLoaded = true; };
    signatureImg.src = signature;
    // Global coordinates for 3-box layout
    var signatureCoordinates = null;

    function pdf(url) {
        var pdfDoc = null,
            pageNum = 1,
            pageRendering = false,
            pageNumPending = null,
            scale = 1.5,
            pdfCanvas = document.getElementById('pdf-render'),
            pdfCtx = pdfCanvas.getContext('2d'),
            markCanvas = document.getElementById('mark-layer'),
            markCtx = markCanvas.getContext('2d'),
            selectPage = document.getElementById('page-select');

        var markCoordinates = null;

        document.getElementById('manager-save').disabled = true;

        function renderPage(num) {
            pageRendering = true;

            pdfDoc.getPage(num).then(function(page) {
                let viewport = page.getViewport({
                    scale: scale
                });
                pdfCanvas.height = viewport.height;
                pdfCanvas.width = viewport.width;
                markCanvas.height = viewport.height;
                markCanvas.width = viewport.width;

                let renderContext = {
                    canvasContext: pdfCtx,
                    viewport: viewport
                };
                let renderTask = page.render(renderContext);

                renderTask.promise.then(function() {
                    pageRendering = false;
                    if (pageNumPending !== null) {
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                });
            });

            selectPage.value = num;
        }

        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        function onNextPage() {
            if (pageNum >= pdfDoc.numPages) {
                return;
            }
            pageNum++;
            queueRenderPage(pageNum);
        }

        function onPrevPage() {
            if (pageNum <= 1) {
                return;
            }
            pageNum--;
            queueRenderPage(pageNum);
        }

        selectPage.addEventListener('change', function() {
            let selectedPage = parseInt(this.value);
            if (selectedPage && selectedPage >= 1 && selectedPage <= pdfDoc.numPages) {
                pageNum = selectedPage;
                queueRenderPage(selectedPage);
            }
        });

        pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            for (let i = 1; i <= pdfDoc.numPages; i++) {
                let option = document.createElement('option');
                option.value = i;
                option.textContent = i;
                selectPage.appendChild(option);
            }

            renderPage(pageNum);
            document.getElementById('manager-sinature').disabled = false;
        });


        document.getElementById('next').addEventListener('click', onNextPage);
        document.getElementById('prev').addEventListener('click', onPrevPage);


        let markEventListener = null;

        function countLineBreaks(text) {
            var lines = text.split('\n');
            return lines.length - 1;
        }
        // ฟังก์ชันในการวาดกากบาทเล็กๆ ที่มุมขวาบน
        function drawMark(startX, startY, endX, endY) {
            var markCanvas = document.getElementById('mark-layer');
            var markCtx = markCanvas.getContext('2d');
            markCtx.clearRect(0, 0, markCanvas.width, markCanvas.height);

            markCtx.beginPath();
            markCtx.rect(startX, startY, endX - startX, endY - startY);
            markCtx.lineWidth = 1;
            markCtx.strokeStyle = 'blue';
            markCtx.stroke();

            var crossSize = 10;
            markCtx.beginPath();
            markCtx.moveTo(endX - crossSize, startY + crossSize);
            markCtx.lineTo(endX, startY);
            markCtx.moveTo(endX, startY + crossSize);
            markCtx.lineTo(endX - crossSize, startY);
            markCtx.lineWidth = 2;
            markCtx.strokeStyle = 'red';
            markCtx.stroke();

            markCanvas.addEventListener('click', function(event) {
                var rect = markCanvas.getBoundingClientRect();
                var clickX = event.clientX - rect.left;
                var clickY = event.clientY - rect.top;

                if (
                    clickX >= endX - crossSize && clickX <= endX &&
                    clickY >= startY && clickY <= startY + crossSize
                ) {
                    removeMarkListener();
                    var markCtx = markCanvas.getContext('2d');
                    markCtx.clearRect(0, 0, markCanvas.width, markCanvas.height);
                }
            });
        }

        function drawMarkSignature(startX, startY, endX, endY, checkedValues) {
            var markCanvas = document.getElementById('mark-layer');
            var markCtx = markCanvas.getContext('2d');
            markCtx.clearRect(0, 0, markCanvas.width, markCanvas.height);

            checkedValues.forEach(element => {
                if (element == 4) {
                    var img = new Image();
                    img.src = signature;
                    img.onload = function() {
                        var imgWidth = 240;
                        var imgHeight = 130;

                        var centeredX = (startX + 50) - (imgWidth / 2);
                        var centeredY = (startY + 60) - (imgHeight / 2);

                        markCtx.drawImage(img, centeredX, centeredY, imgWidth, imgHeight);

                        imgData = {
                            x: centeredX,
                            y: centeredY,
                            width: imgWidth,
                            height: imgHeight
                        };
                    }
                }
            });
        }

        function drawTextHeader(type, startX, startY, text) {
            var markCanvas = document.getElementById('mark-layer');
            var markCtx = markCanvas.getContext('2d');

            markCtx.font = type;
            markCtx.fillStyle = "blue";
            var textWidth = markCtx.measureText(text).width;

            var centeredX = startX - (textWidth / 2);

            markCtx.fillText(text, centeredX, startY);
        }

        function drawTextHeaderSignature(type, startX, startY, text) {
            var markCanvas = document.getElementById('mark-layer');
            var markCtx = markCanvas.getContext('2d');

            markCtx.font = type;
            markCtx.fillStyle = "blue";

            var lines = text.split('\n');
            var lineHeight = 20;

            for (var i = 0; i < lines.length; i++) {
                // 🔴 คำนวณความกว้างของแต่ละบรรทัด
                var textWidth = markCtx.measureText(lines[i]).width;
                var centeredX = startX - (textWidth / 2);

                markCtx.fillText(lines[i], centeredX, startY + (i * lineHeight)); // 🔴 เปลี่ยน startX → centeredX
            }
        }
        $('#modalForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $('#exampleModal').modal('hide');
            Swal.showLoading();
            $.ajax({
                type: "post",
                url: "/book/confirm_signature",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        $('#exampleModal').modal('hide');
                        setTimeout(() => {
                            swal.close();
                        }, 1500);
                        resetMarking();
                        removeMarkListener();
                        document.getElementById('manager-save').disabled = false;

                        markEventListener = function(){
                            var markCanvas = document.getElementById('mark-layer');
                            var markCtx = markCanvas.getContext('2d');

                            if (!signatureCoordinates) {
                                var defaultTextWidth = 220, defaultTextHeight = 40, defaultBottomBoxHeight = 80;
                                var defaultImageWidth = 240, defaultImageHeight = 130, gap = 10;
                                var startX = (markCanvas.width - defaultTextWidth) / 2;
                                var totalH = defaultTextHeight + gap + defaultImageHeight + gap + defaultBottomBoxHeight + 20;
                                var startY = (markCanvas.height - totalH) / 2;
                                signatureCoordinates = {
                                    textBox: { startX: startX, startY: startY, endX: startX + defaultTextWidth, endY: startY + defaultTextHeight, type: 'text' },
                                    imageBox: { startX: startX - 13, startY: startY + defaultTextHeight + gap, endX: (startX - 13) + defaultImageWidth, endY: startY + defaultTextHeight + gap + defaultImageHeight, type: 'image' },
                                    bottomBox:{ startX: startX, startY: startY + defaultTextHeight + gap + defaultImageHeight + gap, endX: startX + defaultTextWidth, endY: startY + defaultTextHeight + gap + defaultImageHeight + gap + defaultBottomBoxHeight, type: 'bottom' }
                                };
                                $('#positionX').val(startX); $('#positionY').val(startY); $('#positionPages').val(1);
                            }

                            var resizeHandleSize = 16;
                            function redraw(){
                                markCtx.clearRect(0,0,markCanvas.width, markCanvas.height);
                                var text = $('#modal-text').val();
                                var checkedValues = $('input[type="checkbox"]:checked').map(function(){return $(this).val();}).get();
                                // text box
                                var tb = signatureCoordinates.textBox;
                                markCtx.save(); markCtx.strokeStyle='blue'; markCtx.lineWidth=0.5; markCtx.strokeRect(tb.startX, tb.startY, tb.endX-tb.startX, tb.endY-tb.startY);
                                markCtx.fillStyle='#fff'; markCtx.strokeStyle='#007bff'; markCtx.lineWidth=2; markCtx.fillRect(tb.endX-resizeHandleSize, tb.endY-resizeHandleSize, resizeHandleSize, resizeHandleSize); markCtx.strokeRect(tb.endX-resizeHandleSize, tb.endY-resizeHandleSize, resizeHandleSize, resizeHandleSize); markCtx.restore();
                                var s = Math.min((tb.endX-tb.startX)/220,(tb.endY-tb.startY)/40); s=Math.max(0.5, Math.min(2.5,s));
                                drawTextHeaderSignature((15*s).toFixed(1)+'px Sarabun', (tb.startX+tb.endX)/2, tb.startY+25*s, text);
                                // image box if selected
                                var hasImg = checkedValues.includes('4');
                                if (hasImg){ var ib = signatureCoordinates.imageBox; markCtx.save(); markCtx.strokeStyle='green'; markCtx.lineWidth=0.5; markCtx.strokeRect(ib.startX, ib.startY, ib.endX-ib.startX, ib.endY-ib.startY); markCtx.fillStyle='#fff'; markCtx.strokeStyle='#28a745'; markCtx.lineWidth=2; markCtx.fillRect(ib.endX-resizeHandleSize, ib.endY-resizeHandleSize, resizeHandleSize, resizeHandleSize); markCtx.strokeRect(ib.endX-resizeHandleSize, ib.endY-resizeHandleSize, resizeHandleSize, resizeHandleSize); markCtx.restore(); var iw=ib.endX-ib.startX, ih=ib.endY-ib.startY; if (signatureImgLoaded){ markCtx.drawImage(signatureImg, ib.startX, ib.startY, iw, ih); imgData={x:ib.startX,y:ib.startY,width:iw,height:ih}; } }
                                // bottom box
                                var bb = signatureCoordinates.bottomBox; markCtx.save(); markCtx.strokeStyle='purple'; markCtx.lineWidth=0.5; markCtx.strokeRect(bb.startX, bb.startY, bb.endX-bb.startX, bb.endY-bb.startY); markCtx.fillStyle='#fff'; markCtx.strokeStyle='#6f42c1'; markCtx.lineWidth=2; markCtx.fillRect(bb.endX-resizeHandleSize, bb.endY-resizeHandleSize, resizeHandleSize, resizeHandleSize); markCtx.strokeRect(bb.endX-resizeHandleSize, bb.endY-resizeHandleSize, resizeHandleSize, resizeHandleSize); markCtx.restore(); var bs=Math.min((bb.endX-bb.startX)/220,(bb.endY-bb.startY)/80); bs=Math.max(0.5, Math.min(2.5,bs)); var i=0; checkedValues.forEach(function(el){ if (el!='4'){ var t=''; switch(el){ case '1': t=`({{$users->fullname}})`; break; case '2': t=`{{$permission_data->permission_name}}`; break; case '3': t=`{{convertDateToThai(date("Y-m-d"))}}`; break; } drawTextHeaderSignature((15*bs).toFixed(1)+'px Sarabun', (bb.startX+bb.endX)/2, bb.startY+25*bs + (20*i*bs), t); i++; } });
                            }
                            var isDragging=false, isResizing=false, activeBox=null, dragOffsetX=0, dragOffsetY=0;
                            function onResizeHandle(x,y,b){ return x>=b.endX-16 && x<=b.endX && y>=b.endY-16 && y<=b.endY; }
                            function inBox(x,y,b){ return x>=b.startX && x<=b.endX && y>=b.startY && y<=b.endY; }
                            function pickBox(x,y){ var cv=$('input[type="checkbox"]:checked').map(function(){return $(this).val();}).get(); var has=cv.includes('4'); if (inBox(x,y, signatureCoordinates.bottomBox)) return signatureCoordinates.bottomBox; if (has && inBox(x,y, signatureCoordinates.imageBox)) return signatureCoordinates.imageBox; if (inBox(x,y, signatureCoordinates.textBox)) return signatureCoordinates.textBox; return null; }
                            markCanvas.addEventListener('mousemove', function(e){ var r=markCanvas.getBoundingClientRect(); var x=e.clientX-r.left; var y=e.clientY-r.top; var cv=$('input[type="checkbox"]:checked').map(function(){return $(this).val();}).get(); var has=cv.includes('4'); if (onResizeHandle(x,y, signatureCoordinates.textBox) || onResizeHandle(x,y, signatureCoordinates.bottomBox) || (has && onResizeHandle(x,y, signatureCoordinates.imageBox))) markCanvas.style.cursor='se-resize'; else if (pickBox(x,y)) markCanvas.style.cursor='move'; else markCanvas.style.cursor='default'; });
                            markCanvas.onmousedown=function(e){ var r=markCanvas.getBoundingClientRect(); var x=e.clientX-r.left; var y=e.clientY-r.top; var cv=$('input[type="checkbox"]:checked').map(function(){return $(this).val();}).get(); var has=cv.includes('4'); if (onResizeHandle(x,y, signatureCoordinates.textBox)) { isResizing=true; activeBox=signatureCoordinates.textBox; e.preventDefault(); window.addEventListener('mousemove', onResizeMove); window.addEventListener('mouseup', onResizeEnd);} else if (onResizeHandle(x,y, signatureCoordinates.bottomBox)) { isResizing=true; activeBox=signatureCoordinates.bottomBox; e.preventDefault(); window.addEventListener('mousemove', onResizeMove); window.addEventListener('mouseup', onResizeEnd);} else if (has && onResizeHandle(x,y, signatureCoordinates.imageBox)) { isResizing=true; activeBox=signatureCoordinates.imageBox; e.preventDefault(); window.addEventListener('mousemove', onResizeMove); window.addEventListener('mouseup', onResizeEnd);} else { activeBox=pickBox(x,y); if (activeBox){ isDragging=true; dragOffsetX=x-activeBox.startX; dragOffsetY=y-activeBox.startY; e.preventDefault(); window.addEventListener('mousemove', onDragMove); window.addEventListener('mouseup', onDragEnd);} }
                            };
                            function onDragMove(e){ if(!isDragging||!activeBox) return; var r=markCanvas.getBoundingClientRect(); var x=e.clientX-r.left; var y=e.clientY-r.top; var w=activeBox.endX-activeBox.startX; var h=activeBox.endY-activeBox.startY; var nsx=Math.max(0, Math.min(markCanvas.width-w, x-dragOffsetX)); var nsy=Math.max(0, Math.min(markCanvas.height-h, y-dragOffsetY)); activeBox.startX=nsx; activeBox.startY=nsy; activeBox.endX=nsx+w; activeBox.endY=nsy+h; if (activeBox.type==='text'){ $('#positionX').val(nsx); $('#positionY').val(nsy);} redraw(); }
                            function onDragEnd(){ isDragging=false; activeBox=null; window.removeEventListener('mousemove', onDragMove); window.removeEventListener('mouseup', onDragEnd); }
                            function onResizeMove(e){ if(!isResizing||!activeBox) return; var r=markCanvas.getBoundingClientRect(); var x=e.clientX-r.left; var y=e.clientY-r.top; var minW=40, minH=30; activeBox.endX=Math.min(markCanvas.width, Math.max(activeBox.startX+minW, x)); activeBox.endY=Math.min(markCanvas.height, Math.max(activeBox.startY+minH, y)); redraw(); }
                            function onResizeEnd(){ isResizing=false; activeBox=null; window.removeEventListener('mousemove', onResizeMove); window.removeEventListener('mouseup', onResizeEnd); }
                            redraw();
                        };

                        var markCanvas = document.getElementById('mark-layer');
                        try { markEventListener(); } catch (e) {}
                        markCanvas.addEventListener('click', markEventListener);
                    } else {
                        $('#exampleModal').modal('hide');
                        Swal.fire("", response.message, "error");
                    }
                }
            });
        });
    }

    let markEventListener = null;

    function openPdf(url, id, status, type, is_number, number, position_id) {
        $('.btn-default').hide();
        document.getElementById('manager-sinature').disabled = false;
        document.getElementById('save-stamp').disabled = true;
        document.getElementById('send-save').disabled = true;
        $('#div-canvas').html('<div style="position: relative;"><canvas id="pdf-render"></canvas><canvas id="mark-layer" style="position: absolute; left: 0; top: 0;"></canvas></div>');
        // cache-busting to always fetch latest PDF
        pdf(url + (url.indexOf('?')>-1?'&':'?') + 'cb=' + Date.now());
        // remember last open args for refresh
        lastOpen = { url:url, id:id, status:status, type:type, is_number:is_number, number:number, position_id:position_id };
        $('#id').val(id);
        $('#position_id').val(position_id);
        $('#positionX').val('');
        $('#positionY').val('');
        $('#txt_label').text('');
        $('#users_id').val('');
        document.getElementById('manager-save').disabled = true;
        if (status == 10) {
            $('#manager-sinature').show();
            $('#manager-save').show();
        }
        if (status == 11) {
            $('#manager-send').show();
            $('#send-save').show();
        }
        resetMarking();
        removeMarkListener();
    }

    function resetMarking() {
        var markCanvas = document.getElementById('mark-layer');
        var markCtx = markCanvas.getContext('2d');
        markCtx.clearRect(0, 0, markCanvas.width, markCanvas.height);
    }

    function removeMarkListener() {
        var markCanvas = document.getElementById('mark-layer');
        if (markEventListener) {
            markCanvas.removeEventListener('click', markEventListener);
            markEventListener = null;
        }
    }

    function resetMarking() {
        var markCanvas = document.getElementById('mark-layer');
        var markCtx = markCanvas.getContext('2d');
        markCtx.clearRect(0, 0, markCanvas.width, markCanvas.height);
    }

    selectPageTable.addEventListener('change', function() {
        let selectedPage = parseInt(this.value);
        ajaxTable(selectedPage);
    });

    function onNextPageTable() {
        if (pageNumTalbe >= pageTotal) {
            return;
        }
        pageNumTalbe++;
        selectPageTable.value = pageNumTalbe;
        ajaxTable(pageNumTalbe);
    }

    function onPrevPageTable() {
        if (pageNumTalbe <= 1) {
            return;
        }
        pageNumTalbe--;
        selectPageTable.value = pageNumTalbe;
        ajaxTable(pageNumTalbe);
    }
    document.getElementById('nextPage').addEventListener('click', onNextPageTable);
    document.getElementById('prevPage').addEventListener('click', onPrevPageTable);

    function ajaxTable(pages) {
        $('#id').val('');
        $('#positionX').val('');
        $('#positionY').val('');
        $('#txt_label').text('');
        $('#users_id').val('');
        document.getElementById('manager-sinature').disabled = false;
        document.getElementById('manager-save').disabled = true;
        $.ajax({
            type: "post",
            url: "/book/dataList",
            data: {
                pages: pages,
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            dataType: "json",
            success: function(response) {
                if (response.status == true) {
                    $('#box-card-item').empty();
                    $('#div-canvas').html('<div style="position: relative;"><canvas id="pdf-render"></canvas><canvas id="mark-layer" style="position: absolute; left: 0; top: 0;"></canvas></div>');
                    response.book.forEach(element => {
                        $html = '<a href="javascript:void(0)" onclick="openPdf(' + "'" + element.url + "'" + ',' + "'" + element.id + "'" + ',' + "'" + element.status + "'" + ')"><div class="card border-dark mb-2"><div class="card-header text-dark fw-bold">' + element.inputSubject + '</div><div class="card-body text-dark"><div class="row"><div class="col-9">' + element.selectBookFrom + '</div><div class="col-3 fw-bold">' + element.showTime + ' น.</div></div></div></div></a>';
                        $('#box-card-item').append($html);
                    });
                }
            }
        });
    }

    $('#search_btn').click(function(e) {
        e.preventDefault();
        $('#id').val('');
        $('#positionX').val('');
        $('#positionY').val('');
        $('.btn-default').hide();
        $('#txt_label').text('');
        $('#users_id').val('');
        document.getElementById('manager-sinature').disabled = false;
        document.getElementById('manager-save').disabled = true;
        $.ajax({
            type: "post",
            url: "/book/dataListSearch",
            data: {
                pages: 1,
                search: $('#inputSearch').val()
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            dataType: "json",
            success: function(response) {
                if (response.status == true) {
                    $('#box-card-item').html('');
                    $('#div-canvas').html('<div style="position: relative;"><canvas id="pdf-render"></canvas><canvas id="mark-layer" style="position: absolute; left: 0; top: 0;"></canvas></div>');
                    pageNumTalbe = 1;
                    pageTotal = response.totalPages;
                    response.book.forEach(element => {
                        $html = '<a href="javascript:void(0)" onclick="openPdf(' + "'" + element.url + "'" + ',' + "'" + element.id + "'" + ',' + "'" + element.status + "'" + ')"><div class="card border-dark mb-2"><div class="card-header text-dark fw-bold">' + element.inputSubject + '</div><div class="card-body text-dark"><div class="row"><div class="col-9">' + element.selectBookFrom + '</div><div class="col-3 fw-bold">' + element.showTime + ' น.</div></div></div></div></a>';
                        $('#box-card-item').append($html);
                    });
                    $("#page-select-card").empty();
                    for (let index = 1; index <= pageTotal; index++) {
                        $('#page-select-card').append('<option value="' + index + '">' + index + '</option>');
                    }
                }
            }
        });
    });

    $('#manager-save').click(function(e) {
        e.preventDefault();
        var id = $('#id').val();
        var position_id = $('#position_id').val();
        var positionX = $('#positionX').val();
        var positionY = $('#positionY').val();
        var pages = $('#page-select').find(":selected").val();
        var text = $('#modal-text').val();
        var checkedValues = $('input[type="checkbox"]:checked').map(function() {
            return $(this).val();
        }).get();
        if (id != '' && positionX != '' && positionY != '') {
            Swal.fire({
                title: "ยืนยันการลงลายเซ็น",
                showCancelButton: true,
                confirmButtonText: "ตกลง",
                cancelButtonText: `ยกเลิก`,
                icon: 'question'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "post",
                        url: "/book/manager_stamp",
                        data: {
                            id: id,
                            positionX: positionX,
                            positionY: positionY,
                            pages: pages,
                            status: 11,
                            text: text,
                            checkedValues: checkedValues,
                            position_id: position_id
                        },
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status) {
                                Swal.fire("", "บันทึกลายเซ็นเรียบร้อยแล้ว", "success");
                                // Refresh the same document preview with cache-busting instead of full reload
                                try {
                                    if (lastOpen && lastOpen.url) {
                                        openPdf(lastOpen.url + (lastOpen.url.indexOf('?')>-1?'&':'?') + 'cb=' + Date.now(), lastOpen.id, lastOpen.status, lastOpen.type, lastOpen.is_number, lastOpen.number, lastOpen.position_id);
                                    } else {
                                        location.reload();
                                    }
                                } catch (e) { location.reload(); }
                            } else {
                                Swal.fire("", "บันทึกไม่สำเร็จ", "error");
                            }
                        }
                    });
                }
            });
        } else {
            Swal.fire("", "กรุณาเลือกตำแหน่งของตราประทับ", "info");
        }
    });

    $('#manager-send').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            url: "/book/_checkbox_send",
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.fire({
                    title: 'แทงเรื่อง',
                    html: response,
                    allowOutsideClick: false,
                    focusConfirm: true,
                    confirmButtonText: 'ตกลง',
                    showCancelButton: true,
                    cancelButtonText: `ยกเลิก`,
                    preConfirm: () => {
                        var selectedCheckboxes = [];
                        var textCheckboxes = [];
                        $('input[name="flexCheckChecked[]"]:checked').each(function() {
                            selectedCheckboxes.push($(this).val());
                            textCheckboxes.push($(this).next('label').text().trim());
                        });

                        console.log(selectedCheckboxes);
                        if (selectedCheckboxes.length === 0) {
                            Swal.showValidationMessage('กรุณาเลือกตัวเลือก');
                        }

                        return {
                            id: selectedCheckboxes,
                            text: textCheckboxes
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        var id = '';
                        var txt = '- แทงเรื่อง ('
                        for (let index = 0; index < result.value.text.length; index++) {
                            if (index > 0 && index < result.value.text.length) {
                                txt += ',';
                            }
                            txt += result.value.text[index];
                        }
                        for (let index = 0; index < result.value.id.length; index++) {
                            if (index > 0 && index < result.value.id.length) {
                                id += ',';
                            }
                            id += result.value.id[index];
                        }
                        txt += ') -';
                        $('#txt_label').text(txt);
                        $('#users_id').val(id);
                        document.getElementById('send-save').disabled = false;
                    }
                });
            }
        });
    });

    $('#send-save').click(function(e) {
        e.preventDefault();
        var id = $('#id').val();
        var users_id = $('#users_id').val();
        var position_id = $('#position_id').val();
        Swal.fire({
            title: "ยืนยันการแทงเรื่อง",
            showCancelButton: true,
            confirmButtonText: "ตกลง",
            cancelButtonText: `ยกเลิก`,
            icon: 'question'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "/book/send_to_save",
                    data: {
                        id: id,
                        users_id: users_id,
                        status: 12,
                        position_id: position_id
                    },
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            if (response.status) {
                                Swal.fire("", "แทงเรื่องเรียบร้อยแล้ว", "success");
                                try {
                                    if (lastOpen && lastOpen.url) {
                                        openPdf(lastOpen.url + (lastOpen.url.indexOf('?')>-1?'&':'?') + 'cb=' + Date.now(), lastOpen.id, lastOpen.status, lastOpen.type, lastOpen.is_number, lastOpen.number, lastOpen.position_id);
                                    } else {
                                        location.reload();
                                    }
                                } catch (e) { location.reload(); }
                            } else {
                                Swal.fire("", "แทงเรื่องไม่สำเร็จ", "error");
                            }
                        }
                    }
                });
            }
        });
    });
    $('#signature-save').click(function(e) {
        e.preventDefault();
        var id = $('#id').val();
        var pages = $('#page-select').find(":selected").val();
        var text = $('#modal-text').val();
        var checkedValues = $('input[type="checkbox"]:checked').map(function() { return $(this).val(); }).get();
        var positionPages = $('#positionPages').val() || 1;

        // Prefer coordinates from 3-box layout
        var textBox = (typeof signatureCoordinates !== 'undefined' && signatureCoordinates) ? signatureCoordinates.textBox : null;
        var imageBox = (typeof signatureCoordinates !== 'undefined' && signatureCoordinates) ? signatureCoordinates.imageBox : null;
        var bottomBox = (typeof signatureCoordinates !== 'undefined' && signatureCoordinates) ? signatureCoordinates.bottomBox : null;
        var positionX = null, positionY = null, width = null, height = null;
        if (textBox) {
            positionX = textBox.startX;
            positionY = textBox.startY;
            width = textBox.endX - textBox.startX;
            height = textBox.endY - textBox.startY;
        } else {
            // fallback legacy
            positionX = $('#positionX').val();
            positionY = $('#positionY').val();
        }

        if (id && positionX !== '' && positionY !== '') {
            Swal.fire({
                title: "ยืนยันการลงเกษียณหนังสือ",
                showCancelButton: true,
                confirmButtonText: "ตกลง",
                cancelButtonText: `ยกเลิก`,
                icon: 'question'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "post",
                        url: "/book/signature_stamp",
                        data: (function(){
                            var payload = {
                                id: id,
                                positionX: positionX,
                                positionY: positionY,
                                pages: pages,
                                positionPages: positionPages,
                                text: text,
                                checkedValues: checkedValues
                            };
                            if (width != null && height != null) { payload.width = width; payload.height = height; }
                            if (bottomBox) {
                                payload.bottomBox = {
                                    startX: bottomBox.startX,
                                    startY: bottomBox.startY,
                                    width: bottomBox.endX - bottomBox.startX,
                                    height: bottomBox.endY - bottomBox.startY
                                };
                            }
                            if (imageBox && checkedValues.includes('4')) {
                                payload.imageBox = {
                                    startX: imageBox.startX,
                                    startY: imageBox.startY,
                                    width: imageBox.endX - imageBox.startX,
                                    height: imageBox.endY - imageBox.startY
                                };
                            }
                            return payload;
                        })(),
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status) {
                                Swal.fire("", "ลงบันทึกเกษียณหนังสือเรียบร้อย", "success");
                                try {
                                    if (lastOpen && lastOpen.url) {
                                        openPdf(lastOpen.url + (lastOpen.url.indexOf('?')>-1?'&':'?') + 'cb=' + Date.now(), lastOpen.id, lastOpen.status, lastOpen.type, lastOpen.is_number, lastOpen.number, lastOpen.position_id);
                                    } else {
                                        location.reload();
                                    }
                                } catch (e) { location.reload(); }
                            } else {
                                Swal.fire("", "บันทึกไม่สำเร็จ", "error");
                            }
                        }
                    });
                }
            });
        } else {
            Swal.fire("", "กรุณาเลือกตำแหน่งเกษียณหนังสือ", "info");
        }
    });
    $(document).ready(function() {
        $('#manager-sinature').click(function(e) {
            e.preventDefault();
            $('#exampleModal').modal('show');
        });
        $('#exampleModal').on('show.bs.modal', function(event) {
            $('input[type="password"]').val('');
            $('textarea').val('');
        });
    });
</script>
<div class="modal modal-lg fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="modalForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">เซ็นหนังสือ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label for="inputPassword" class="col-sm-2 col-form-label"><span class="req">*</span>ข้อความเซ็นหนังสือ :</label>
                        <div class="col-sm-10">
                            <textarea rows="4" class="form-control" name="modal-text" id="modal-text"></textarea>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-2">
                        </div>
                        <div class="col-sm-10 d-flex justify-content-center text-center">
                            ({{$users->fullname}})<br>
                            {{$permission_data->permission_name}}<br>
                            {{convertDateToThai(date("Y-m-d"))}}
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="inputPassword" class="col-sm-2 col-form-label"><span class="req">*</span>รหัสผ่านเกษียน :</label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control" id="modal-Password" name="modal-Password">
                        </div>
                    </div>
                    <div class="row">
                        <label for="inputPassword" class="col-sm-2 col-form-label"><span class="req">*</span>แสดงผล :</label>
                        <div class="col-sm-10 d-flex align-items-center">
                            <ul class="list-group list-group-horizontal">
                                <li class="list-group-item"><input class="form-check-input me-1" type="checkbox" name="modal-check[]" value="1" checked>ชื่อ-นามสกุล</li>
                                <li class="list-group-item"><input class="form-check-input me-1" type="checkbox" name="modal-check[]" value="2" checked>ตำแหน่ง</li>
                                <li class="list-group-item"><input class="form-check-input me-1" type="checkbox" name="modal-check[]" value="3" checked>วันที่</li>
                                <li class="list-group-item"><input class="form-check-input me-1" type="checkbox" name="modal-check[]" value="4" checked>ลายเซ็น</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="submit-modal" class="btn btn-primary">ตกลง</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
