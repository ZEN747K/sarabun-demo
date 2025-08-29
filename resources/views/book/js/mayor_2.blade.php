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
    var lastOpen = null;
    // Preload signature image for 3-box drawing
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
            pdfCanvasInsert = document.getElementById('pdf-render-insert'),
            pdfCtx = pdfCanvas.getContext('2d'),
            pdfCtxInsert = pdfCanvasInsert.getContext('2d'),
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


        // let markEventListener = null;
        function countLineBreaks(text) {
            var lines = text.split('\n');
            return lines.length - 1;
        }

        function drawMarkSignature(startX, startY, endX, endY, checkedValues) {
            var markCanvas = document.getElementById('mark-layer-insert');
            var markCtx = markCanvas.getContext('2d');
            markCtx.clearRect(0, 0, markCanvas.width, markCanvas.height);

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

        function drawMarkSignatureInsert(startX, startY, endX, endY, checkedValues) {
            var markCanvas = document.getElementById('mark-layer');
            var markCtx = markCanvas.getContext('2d');
            markCtx.clearRect(0, 0, markCanvas.width, markCanvas.height);

            var markCanvas = document.getElementById('mark-layer-insert');
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

        function drawTextHeaderSignature(type, startX, startY, text) {
            var markCanvas = document.getElementById('mark-layer');
            var markCtx = markCanvas.getContext('2d');
            markCtx.font = type;
            markCtx.fillStyle = "blue";
            var lines = text.split('\n');
            var lineHeight = 20;
            for (var i = 0; i < lines.length; i++) {
                var textWidth = markCtx.measureText(lines[i]).width;
                var centeredX = startX - (textWidth / 2);
                markCtx.fillText(lines[i], centeredX, startY + (i * lineHeight));
            }
        }

        function drawTextHeaderSignatureInsert(type, startX, startY, text) {
            var markCanvas = document.getElementById('mark-layer-insert');
            var markCtx = markCanvas.getContext('2d');
            markCtx.font = type;
            markCtx.fillStyle = "blue";
            var lines = text.split('\n');
            var lineHeight = 20;
            for (var i = 0; i < lines.length; i++) {
                var textWidth = markCtx.measureText(lines[i]).width;
                var centeredX = startX - (textWidth / 2);
                markCtx.fillText(lines[i], centeredX, startY + (i * lineHeight));
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

                        // Initialize 3-box layout (text → image → bottom) and enable drag/resize
                        markEventListener = function() {
                            var markCanvas = document.getElementById('mark-layer');
                            var markCtx = markCanvas.getContext('2d');

                            if (!signatureCoordinates) {
                                var defaultTextWidth = 220;
                                var defaultTextHeight = 40;
                                var defaultBottomBoxHeight = 80;
                                var defaultImageWidth = 240;
                                var defaultImageHeight = 130;
                                var gap = 10;
                                var startX = (markCanvas.width - defaultTextWidth) / 2;
                                var totalH = defaultTextHeight + gap + defaultImageHeight + gap + defaultBottomBoxHeight + 20;
                                var startY = (markCanvas.height - totalH) / 2;
                                signatureCoordinates = {
                                    textBox: { startX: startX, startY: startY, endX: startX + defaultTextWidth, endY: startY + defaultTextHeight, type: 'text' },
                                    imageBox: { startX: startX - 13, startY: startY + defaultTextHeight + gap, endX: (startX - 13) + defaultImageWidth, endY: startY + defaultTextHeight + gap + defaultImageHeight, type: 'image' },
                                    bottomBox: { startX: startX, startY: startY + defaultTextHeight + gap + defaultImageHeight + gap, endX: startX + defaultTextWidth, endY: startY + defaultTextHeight + gap + defaultImageHeight + gap + defaultBottomBoxHeight, type: 'bottom' }
                                };
                                $('#positionX').val(startX);
                                $('#positionY').val(startY);
                                $('#positionPages').val(1);
                            }

                            var resizeHandleSize = 16;
                            function redrawSignatureBoxes(){
                                markCtx.clearRect(0,0,markCanvas.width, markCanvas.height);
                                var text = $('#modal-text').val();
                                var checkedValues = $('input[type="checkbox"]:checked').map(function(){ return $(this).val(); }).get();
                                // text box
                                var textBox = signatureCoordinates.textBox;
                                markCtx.save(); markCtx.strokeStyle='blue'; markCtx.lineWidth=0.5; markCtx.strokeRect(textBox.startX, textBox.startY, textBox.endX-textBox.startX, textBox.endY-textBox.startY);
                                markCtx.fillStyle='#fff'; markCtx.strokeStyle='#007bff'; markCtx.lineWidth=2; markCtx.fillRect(textBox.endX-resizeHandleSize, textBox.endY-resizeHandleSize, resizeHandleSize, resizeHandleSize); markCtx.strokeRect(textBox.endX-resizeHandleSize, textBox.endY-resizeHandleSize, resizeHandleSize, resizeHandleSize); markCtx.restore();
                                var textScale = Math.min((textBox.endX-textBox.startX)/220,(textBox.endY-textBox.startY)/40); textScale = Math.max(0.5, Math.min(2.5, textScale));
                                drawTextHeaderSignature((15*textScale).toFixed(1)+'px Sarabun', (textBox.startX+textBox.endX)/2, textBox.startY+25*textScale, text);
                                // image box
                                var hasImage = checkedValues.includes('4');
                                if (hasImage) {
                                    var imageBox = signatureCoordinates.imageBox;
                                    markCtx.save(); markCtx.strokeStyle='green'; markCtx.lineWidth=0.5; markCtx.strokeRect(imageBox.startX, imageBox.startY, imageBox.endX-imageBox.startX, imageBox.endY-imageBox.startY);
                                    markCtx.fillStyle='#fff'; markCtx.strokeStyle='#28a745'; markCtx.lineWidth=2; markCtx.fillRect(imageBox.endX-resizeHandleSize, imageBox.endY-resizeHandleSize, resizeHandleSize, resizeHandleSize); markCtx.strokeRect(imageBox.endX-resizeHandleSize, imageBox.endY-resizeHandleSize, resizeHandleSize, resizeHandleSize); markCtx.restore();
                                    var imgW=imageBox.endX-imageBox.startX, imgH=imageBox.endY-imageBox.startY; if (signatureImgLoaded){ markCtx.drawImage(signatureImg, imageBox.startX, imageBox.startY, imgW, imgH); imgData={x:imageBox.startX,y:imageBox.startY,width:imgW,height:imgH}; }
                                }
                                // bottom box
                                var bottomBox = signatureCoordinates.bottomBox;
                                markCtx.save(); markCtx.strokeStyle='purple'; markCtx.lineWidth=0.5; markCtx.strokeRect(bottomBox.startX, bottomBox.startY, bottomBox.endX-bottomBox.startX, bottomBox.endY-bottomBox.startY);
                                markCtx.fillStyle='#fff'; markCtx.strokeStyle='#6f42c1'; markCtx.lineWidth=2; markCtx.fillRect(bottomBox.endX-resizeHandleSize, bottomBox.endY-resizeHandleSize, resizeHandleSize, resizeHandleSize); markCtx.strokeRect(bottomBox.endX-resizeHandleSize, bottomBox.endY-resizeHandleSize, resizeHandleSize, resizeHandleSize); markCtx.restore();
                                var bottomScale = Math.min((bottomBox.endX-bottomBox.startX)/220,(bottomBox.endY-bottomBox.startY)/80); bottomScale = Math.max(0.5, Math.min(2.5, bottomScale));
                                var i=0; checkedValues.forEach(function(el){ if (el!='4'){ var t=''; switch(el){ case '1': t=`({{$users->fullname}})`; break; case '2': t=`{{$permission_data->permission_name}}`; break; case '3': t=`{{convertDateToThai(date("Y-m-d"))}}`; break; } drawTextHeaderSignature((15*bottomScale).toFixed(1)+'px Sarabun', (bottomBox.startX+bottomBox.endX)/2, bottomBox.startY + 25*bottomScale + (20*i*bottomScale), t); i++; } });
                            }
                            var isDragging=false, isResizing=false, activeBox=null, dragOffsetX=0, dragOffsetY=0;
                            function isOnResizeHandle(x,y,box){ return x>=box.endX-16 && x<=box.endX && y>=box.endY-16 && y<=box.endY; }
                            function isInBox(x,y,box){ return x>=box.startX && x<=box.endX && y>=box.startY && y<=box.endY; }
                            function getActiveBox(x,y){ var checked=$('input[type="checkbox"]:checked').map(function(){return $(this).val();}).get(); var hasImg=checked.includes('4'); if (isInBox(x,y, signatureCoordinates.bottomBox)) return signatureCoordinates.bottomBox; if (hasImg && isInBox(x,y, signatureCoordinates.imageBox)) return signatureCoordinates.imageBox; if (isInBox(x,y, signatureCoordinates.textBox)) return signatureCoordinates.textBox; return null; }
                            markCanvas.addEventListener('mousemove', function(e){ var r=markCanvas.getBoundingClientRect(); var x=e.clientX-r.left; var y=e.clientY-r.top; var checked=$('input[type="checkbox"]:checked').map(function(){return $(this).val();}).get(); var hasImg=checked.includes('4'); if (isOnResizeHandle(x,y, signatureCoordinates.textBox) || isOnResizeHandle(x,y, signatureCoordinates.bottomBox) || (hasImg && isOnResizeHandle(x,y, signatureCoordinates.imageBox))) markCanvas.style.cursor='se-resize'; else if (getActiveBox(x,y)) markCanvas.style.cursor='move'; else markCanvas.style.cursor='default'; });
                            markCanvas.onmousedown=function(e){ var r=markCanvas.getBoundingClientRect(); var x=e.clientX-r.left; var y=e.clientY-r.top; var checked=$('input[type="checkbox"]:checked').map(function(){return $(this).val();}).get(); var hasImg=checked.includes('4'); if (isOnResizeHandle(x,y, signatureCoordinates.textBox)) { isResizing=true; activeBox=signatureCoordinates.textBox; e.preventDefault(); window.addEventListener('mousemove', onResizeMove); window.addEventListener('mouseup', onResizeEnd);} else if (isOnResizeHandle(x,y, signatureCoordinates.bottomBox)) { isResizing=true; activeBox=signatureCoordinates.bottomBox; e.preventDefault(); window.addEventListener('mousemove', onResizeMove); window.addEventListener('mouseup', onResizeEnd);} else if (hasImg && isOnResizeHandle(x,y, signatureCoordinates.imageBox)) { isResizing=true; activeBox=signatureCoordinates.imageBox; e.preventDefault(); window.addEventListener('mousemove', onResizeMove); window.addEventListener('mouseup', onResizeEnd);} else { activeBox=getActiveBox(x,y); if (activeBox){ isDragging=true; dragOffsetX=x-activeBox.startX; dragOffsetY=y-activeBox.startY; e.preventDefault(); window.addEventListener('mousemove', onDragMove); window.addEventListener('mouseup', onDragEnd);} }
                            };
                            function onDragMove(e){ if(!isDragging||!activeBox) return; var r=markCanvas.getBoundingClientRect(); var x=e.clientX-r.left; var y=e.clientY-r.top; var w=activeBox.endX-activeBox.startX; var h=activeBox.endY-activeBox.startY; var nsx=Math.max(0, Math.min(markCanvas.width-w, x-dragOffsetX)); var nsy=Math.max(0, Math.min(markCanvas.height-h, y-dragOffsetY)); activeBox.startX=nsx; activeBox.startY=nsy; activeBox.endX=nsx+w; activeBox.endY=nsy+h; if (activeBox.type==='text'){ $('#positionX').val(nsx); $('#positionY').val(nsy);} redrawSignatureBoxes(); }
                            function onDragEnd(){ isDragging=false; activeBox=null; window.removeEventListener('mousemove', onDragMove); window.removeEventListener('mouseup', onDragEnd); }
                            function onResizeMove(e){ if(!isResizing||!activeBox) return; var r=markCanvas.getBoundingClientRect(); var x=e.clientX-r.left; var y=e.clientY-r.top; var minW=40, minH=30; activeBox.endX=Math.min(markCanvas.width, Math.max(activeBox.startX+minW, x)); activeBox.endY=Math.min(markCanvas.height, Math.max(activeBox.startY+minH, y)); redrawSignatureBoxes(); }
                            function onResizeEnd(){ isResizing=false; activeBox=null; window.removeEventListener('mousemove', onResizeMove); window.removeEventListener('mouseup', onResizeEnd); }
                            redrawSignatureBoxes();
                        };

                        var markCanvas = document.getElementById('mark-layer');
                        try { markEventListener(); } catch (e) {}
                        markCanvas.addEventListener('click', markEventListener);
                        markCanvas.addEventListener('click', markEventListener);

                        markEventListenerInsert = function(e) {
                            var markCanvas = document.getElementById('mark-layer-insert');
                            var markCtx = markCanvas.getContext('2d');
                            var rect = markCanvas.getBoundingClientRect();
                            var startX = (e.clientX - rect.left);
                            var startY = (e.clientY - rect.top);

                            var endX = startX + 213;
                            var endY = startY + 115;

                            markCoordinates = {
                                startX,
                                startY,
                                endX,
                                endY
                            };
                            $('#positionX').val(startX);
                            $('#positionY').val(startY);
                            $('#positionPages').val(2);

                            var text = $('#modal-text').val();
                            var lineBreakCount = countLineBreaks(text);
                            var checkedValues = $('input[type="checkbox"]:checked').map(function() {
                                return $(this).val();
                            }).get();
                            drawMarkSignatureInsert(startX - 40, startY + (20 * lineBreakCount), endX, endY, checkedValues);
                            drawTextHeaderSignatureInsert('15px Sarabun', startX, startY, text);

                            var i = 0;
                            var checkbox_text = '';
                            var checkbox_x = 0;
                            var plus_y = 20;
                            checkedValues.forEach(element => {
                                if (element == 4) {
                                    plus_y = 160;
                                }
                            });

                            checkedValues.forEach(element => {
                                switch (element) {
                                    case '1':
                                        checkbox_text = `({{$users->fullname}})`;
                                        break;
                                    case '2':
                                        checkbox_text = `{{$permission_data->permission_name}}`;
                                        break;
                                    case '3':
                                        checkbox_text = `{{convertDateToThai(date("Y-m-d"))}}`;
                                        break;
                                }
                                var lines = checkbox_text.split('\n');
                                if (element != 4) {
                                    drawTextHeaderSignatureInsert('15px Sarabun', startX, (startY + plus_y + (20 * lineBreakCount)) + (20 * i), checkbox_text);
                                }
                                if (lines.length > 1) {
                                    var stop = 0;
                                    lines.forEach(element => {
                                        if (stop != 0) {
                                            i++;
                                        }
                                        stop++;
                                    });
                                }
                                i++;
                            });
                        };

                        var markCanvas = document.getElementById('mark-layer-insert');
                        markCanvas.addEventListener('click', markEventListenerInsert);
                    } else {
                        $('#exampleModal').modal('hide');
                        Swal.fire("", response.message, "error");
                    }
                }
            });
        });
    }

    let markEventListener = null;
    let markEventListenerInsert = null;

    function openPdf(url, id, status, type, is_check = '', number_id, position_id) {
        $('.btn-default').hide();
        document.getElementById('manager-sinature').disabled = false;
        document.getElementById('save-stamp').disabled = true;
        document.getElementById('send-save').disabled = true;
        $('#div-canvas').html('<div style="position: relative;"><canvas id="pdf-render"></canvas><canvas id="mark-layer" style="position: absolute; left: 0; top: 0;"></canvas></div>');
        pdf(url + (url.indexOf('?')>-1?'&':'?') + 'cb=' + Date.now());
        lastOpen = { url:url, id:id, status:status, type:type, is_number:is_check, number:number_id, position_id:position_id };
        $('#id').val(id);
        $('#position_id').val(position_id);
        $('#positionX').val('');
        $('#positionY').val('');
        $('#txt_label').text('');
        $('#users_id').val('');
        document.getElementById('manager-save').disabled = true;
        if (status == 12) {
            $('#manager-sinature').show();
            $('#manager-save').show();
            $('#insert-pages').show();
        }
        if (status == 13) {
            $('#manager-send').show();
        }
        resetMarking();
        removeMarkListener();
    }

    function removeMarkListener() {
        var markCanvas = document.getElementById('mark-layer');
        var markCanvasInsert = document.getElementById('mark-layer-insert');
        if (markEventListener) {
            markCanvas.removeEventListener('click', markEventListener);
            markEventListener = null;
        }
        if (markEventListenerInsert) {
            markCanvasInsert.removeEventListener('click', markEventListenerInsert);
            markEventListenerInsert = null;
        }
    }

    function resetMarking() {
        var markCanvas = document.getElementById('mark-layer');
        var markCanvasInsert = document.getElementById('mark-layer-insert');
        var markCtx = markCanvas.getContext('2d');
        var markCtxInsert = markCanvasInsert.getContext('2d');
        markCtx.clearRect(0, 0, markCanvas.width, markCanvas.height);
        markCtxInsert.clearRect(0, 0, markCanvasInsert.width, markCanvasInsert.height);
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
                        var color = 'info';
                        if (element.type != 1) {
                            var color = 'warning';
                        }
                        $html = '<a href="javascript:void(0)" onclick="openPdf(' + "'" + element.url + "'" + ',' + "'" + element.id + "'" + ',' + "'" + element.status + "'" + ',' + "'" + element.type + "'" + ',' + "'" + element.is_number_stamp + "'" + ',' + "'" + element.inputBookregistNumber + "'" + ',' + "'" + element.position_id + "'" + ')"><div class="card border-' + color + ' mb-2"><div class="card-header text-dark fw-bold">' + element.inputSubject + '</div><div class="card-body text-dark"><div class="row"><div class="col-9">' + element.selectBookFrom + '</div><div class="col-3 fw-bold">' + element.showTime + ' น.</div></div></div></div></a>';
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
                        var color = 'info';
                        if (element.type != 1) {
                            var color = 'warning';
                        }
                        $html = '<a href="javascript:void(0)" onclick="openPdf(' + "'" + element.url + "'" + ',' + "'" + element.id + "'" + ',' + "'" + element.status + "'" + ',' + "'" + element.type + "'" + ',' + "'" + element.is_number_stamp + "'" + ',' + "'" + element.inputBookregistNumber + "'" + ',' + "'" + element.position_id + "'" + ')"><div class="card border-' + color + ' mb-2"><div class="card-header text-dark fw-bold">' + element.inputSubject + '</div><div class="card-body text-dark"><div class="row"><div class="col-9">' + element.selectBookFrom + '</div><div class="col-3 fw-bold">' + element.showTime + ' น.</div></div></div></div></a>';
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
        var positionPages = $('#positionPages').val();
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
                            positionPages: positionPages,
                            status: 14,
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
                                try {
                                    if (typeof lastOpen !== 'undefined' && lastOpen && lastOpen.url) {
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
        var id = $('#id').val();
        var position_id = $('#position_id').val();
        Swal.fire({
            title: 'ท่านต้องการส่งเวียนหนังสือใช่หรือไม่',
            html: '',
            icon: 'question',
            allowOutsideClick: false,
            focusConfirm: true,
            confirmButtonText: 'ตกลง',
            showCancelButton: true,
            cancelButtonText: `ยกเลิก`,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "/book/send_to_save",
                    data: {
                        id: id,
                        status: 14,
                        position_id: position_id
                    },
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            if (response.status) {
                                Swal.fire("", "บันทึกข้อมูลเรียบร้อย", "success");
                                try {
                                    if (typeof lastOpen !== 'undefined' && lastOpen && lastOpen.url) {
                                        openPdf(lastOpen.url + (lastOpen.url.indexOf('?')>-1?'&':'?') + 'cb=' + Date.now(), lastOpen.id, lastOpen.status, lastOpen.type, lastOpen.is_number, lastOpen.number, lastOpen.position_id);
                                    } else {
                                        location.reload();
                                    }
                                } catch (e) { location.reload(); }
                            } else {
                                Swal.fire("", "บันทึกข้อมูลไม่สำเร็จ", "error");
                            }
                        }
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
                        status: 14,
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
                                    if (typeof lastOpen !== 'undefined' && lastOpen && lastOpen.url) {
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
    $(document).ready(function() {
        $('#manager-sinature').click(function(e) {
            e.preventDefault();
        });
        $('#insert-pages').click(function(e) {
            e.preventDefault();
            $('#insert_tab').show();
        });

        async function createAndRenderPDF() {
            const pdfDoc = await PDFLib.PDFDocument.create();
            pdfDoc.addPage([600, 800]);
            const pdfBytes = await pdfDoc.save();

            const loadingTask = pdfjsLib.getDocument({
                data: pdfBytes
            });
            loadingTask.promise.then(pdf => pdf.getPage(1))
                .then(page => {
                    const scale = 1.5;
                    const viewport = page.getViewport({
                        scale
                    });

                    const canvas = document.getElementById("pdf-render-insert");
                    const context = canvas.getContext("2d");
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    return page.render(renderContext).promise;
                }).catch(error => console.error("Error rendering PDF:", error));
        }

        createAndRenderPDF();
    });
</script>
@endsection
