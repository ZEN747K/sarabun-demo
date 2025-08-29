@section('script')
<?php $position = [1 => 'สำนักงานปลัด', 2 => 'งานกิจการสภา', 3 => 'กองคลัง', 4 => 'กองช่าง', 5 => 'กองการศึกษา ศาสนาและวัฒนธรรม', 6 => 'ฝ่ายศูนย์รับเรื่องร้องเรียน-ร้องทุกข์', 7 => 'ฝ่ายเลือกตั้ง', 8 => 'ฝ่ายสปสช.', 9 => 'ศูนย์ข้อมูลข่าวสาร']; ?>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@include('book.js.constants')
@include('book.js.shared-list')
<script>
    $('.btn-default').hide();
    var signature = '{{$signature}}';
    var selectPageTable = document.getElementById('page-select-card');
    var pageTotal = '{{$totalPages}}';
    var pageNumTalbe = 1;

    var imgData = null;
    // Preload signature image and track load state
    var signatureImg = new Image();
    var signatureImgLoaded = false;
    signatureImg.onload = function() { signatureImgLoaded = true; };
    signatureImg.src = signature;
    // Global coordinates for triple-box signature (text, image, bottom)
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

        $('#page-select').off('change').on('change', function() {
            let selectedPage = parseInt(this.value);
            if (selectedPage && selectedPage >= 1 && selectedPage <= pdfDoc.numPages) {
                pageNum = selectedPage;
                queueRenderPage(selectedPage);
            }
        });

        pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            $('#page-select').empty();
            for (let i = 1; i <= pdfDoc.numPages; i++) {
                let option = document.createElement('option');
                option.value = i;
                option.textContent = i;
                selectPage.appendChild(option);
            }

            renderPage(pageNum);
            document.getElementById('manager-sinature').disabled = false;
        });


        $('#next').off('click').on('click', function(e){ e.preventDefault(); onNextPage(); });
        $('#prev').off('click').on('click', function(e){ e.preventDefault(); onPrevPage(); });


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
                        setTimeout(() => { swal.close(); }, 1500);
                        resetMarking();
                        removeMarkListener();
                        document.getElementById('manager-save').disabled = false;

                        // Advanced 3-box draggable/resizable layout (text, image, bottom)
                        markEventListener = function() {
                            var markCanvas = document.getElementById('mark-layer');
                            var markCtx = markCanvas.getContext('2d');

                            // Initialize default boxes once (centered)
                            if (!signatureCoordinates) {
                                var startX = (markCanvas.width - 220) / 2;
                                var startY = (markCanvas.height - (40 + 10 + 130 + 10 + 80)) / 2;

                                var textBox = { type: 'text', startX: startX, startY: startY, endX: startX + 220, endY: startY + 40 };
                                var imageBox = { type: 'image', startX: startX - 10, startY: textBox.endY + 10, endX: startX - 10 + 240, endY: textBox.endY + 10 + 130 };
                                var bottomBox = { type: 'bottom', startX: startX, startY: imageBox.endY + 10, endX: startX + 220, endY: imageBox.endY + 10 + 80 };
                                signatureCoordinates = { textBox: textBox, bottomBox: bottomBox, imageBox: imageBox };

                                // Pre-fill hidden inputs from text box
                                $('#positionX').val(textBox.startX);
                                $('#positionY').val(textBox.startY);
                                $('#positionPages').val(1);
                            }

                            function redrawSignatureBoxes() {
                                markCtx.clearRect(0, 0, markCanvas.width, markCanvas.height);
                                var resizeHandleSize = 16;

                                var textBox = signatureCoordinates.textBox;
                                // Bounding + handle + text (centered per line)
                                markCtx.beginPath();
                                markCtx.rect(textBox.startX, textBox.startY, textBox.endX - textBox.startX, textBox.endY - textBox.startY);
                                markCtx.lineWidth = 0.5;
                                markCtx.strokeStyle = '#3b82f6';
                                markCtx.stroke();
                                markCtx.save();
                                markCtx.fillStyle = '#fff';
                                markCtx.strokeStyle = '#3b82f6';
                                markCtx.lineWidth = 2;
                                markCtx.fillRect(textBox.endX - resizeHandleSize, textBox.endY - resizeHandleSize, resizeHandleSize, resizeHandleSize);
                                markCtx.strokeRect(textBox.endX - resizeHandleSize, textBox.endY - resizeHandleSize, resizeHandleSize, resizeHandleSize);
                                markCtx.restore();
                                // Draw header text
                                var text = $('#modal-text').val() || '';
                                var lines = text.split('\n');
                                markCtx.font = '15px Sarabun';
                                markCtx.fillStyle = 'blue';
                                for (var i = 0; i < lines.length; i++) {
                                    var w = markCtx.measureText(lines[i]).width;
                                    var cx = (textBox.startX + textBox.endX) / 2 - (w / 2);
                                    markCtx.fillText(lines[i], cx, textBox.startY + 20 + (i * 20));
                                }

                                var checkedValues = $('input[type="checkbox"]:checked').map(function() { return $(this).val(); }).get();
                                var hasImage = checkedValues.includes('4');

                                if (hasImage) {
                                    var imageBox = signatureCoordinates.imageBox;
                                    markCtx.beginPath();
                                    markCtx.rect(imageBox.startX, imageBox.startY, imageBox.endX - imageBox.startX, imageBox.endY - imageBox.startY);
                                    markCtx.lineWidth = 0.5;
                                    markCtx.strokeStyle = '#22c55e';
                                    markCtx.stroke();
                                    markCtx.save();
                                    markCtx.fillStyle = '#fff';
                                    markCtx.strokeStyle = '#22c55e';
                                    markCtx.lineWidth = 2;
                                    markCtx.fillRect(imageBox.endX - resizeHandleSize, imageBox.endY - resizeHandleSize, resizeHandleSize, resizeHandleSize);
                                    markCtx.strokeRect(imageBox.endX - resizeHandleSize, imageBox.endY - resizeHandleSize, resizeHandleSize, resizeHandleSize);
                                    markCtx.restore();
                                    // Draw signature image if loaded
                                    if (signatureImgLoaded) {
                                        var iw = imageBox.endX - imageBox.startX;
                                        var ih = imageBox.endY - imageBox.startY;
                                        markCtx.drawImage(signatureImg, imageBox.startX, imageBox.startY, iw, ih);
                                        imgData = { x: imageBox.startX, y: imageBox.startY, width: iw, height: ih };
                                    }
                                }

                                var bottomBox = signatureCoordinates.bottomBox;
                                markCtx.beginPath();
                                markCtx.rect(bottomBox.startX, bottomBox.startY, bottomBox.endX - bottomBox.startX, bottomBox.endY - bottomBox.startY);
                                markCtx.lineWidth = 0.5;
                                markCtx.strokeStyle = '#a855f7';
                                markCtx.stroke();
                                markCtx.save();
                                markCtx.fillStyle = '#fff';
                                markCtx.strokeStyle = '#a855f7';
                                markCtx.lineWidth = 2;
                                markCtx.fillRect(bottomBox.endX - resizeHandleSize, bottomBox.endY - resizeHandleSize, resizeHandleSize, resizeHandleSize);
                                markCtx.strokeRect(bottomBox.endX - resizeHandleSize, bottomBox.endY - resizeHandleSize, resizeHandleSize, resizeHandleSize);
                                markCtx.restore();

                                // Draw bottom texts based on checked values (except image = 4)
                                function wrapByWidth(ctx, text, maxWidth){
                                    var words=(text||'').split(' '), lines=[], line='';
                                    for(var i=0;i<words.length;i++){ var test=line? (line+' '+words[i]) : words[i]; if(ctx.measureText(test).width<=maxWidth){ line=test; } else { if(line){ lines.push(line); } line=words[i]; } }
                                    if(line){ lines.push(line); } return lines;
                                }
                                var i2 = 0;
                                var boxW = (bottomBox.endX - bottomBox.startX) - 8;
                                var bottomScale = Math.min((bottomBox.endX - bottomBox.startX)/213, (bottomBox.endY - bottomBox.startY)/80); bottomScale = Math.max(0.5, Math.min(2.5, bottomScale));
                                checkedValues.forEach(function(v){
                                    if(v!=='4'){
                                        var t='';
                                        switch(v){ case '1': t=`({{$users->fullname}})`; break; case '2': t=`{{$permission_data->permission_name}}`; break; case '3': t=`{{convertDateToThai(date("Y-m-d"))}}`; break; }
                                        if(!t) return;
                                        markCtx.font = (15*bottomScale).toFixed(1)+'px Sarabun';
                                        markCtx.fillStyle='blue';
                                        var lines=[]; t.split('\n').forEach(function(seg){ lines=lines.concat(wrapByWidth(markCtx, seg, boxW)); });
                                        lines.forEach(function(line){ var w=markCtx.measureText(line).width; var cx=(bottomBox.startX+bottomBox.endX)/2 - (w/2); markCtx.fillText(line, cx, bottomBox.startY + 25*bottomScale + (20*i2*bottomScale)); i2++; });
                                    }
                                });
                            }

                            function isOnResizeHandle(mouseX, mouseY, box) {
                                var s = 16;
                                return box && mouseX >= box.endX - s && mouseX <= box.endX && mouseY >= box.endY - s && mouseY <= box.endY;
                            }
                            function isInBox(mouseX, mouseY, box) {
                                return box && mouseX >= box.startX && mouseX <= box.endX && mouseY >= box.startY && mouseY <= box.endY;
                            }
                            function getActiveBox(mouseX, mouseY) {
                                var checkedValues = $('input[type="checkbox"]:checked').map(function () { return $(this).val(); }).get();
                                var hasImage = checkedValues.includes('4');
                                if (isInBox(mouseX, mouseY, signatureCoordinates.bottomBox)) return signatureCoordinates.bottomBox;
                                if (hasImage && isInBox(mouseX, mouseY, signatureCoordinates.imageBox)) return signatureCoordinates.imageBox;
                                if (isInBox(mouseX, mouseY, signatureCoordinates.textBox)) return signatureCoordinates.textBox;
                                return null;
                            }

                            var isDragging = false, isResizing = false, activeBox = null, dragOffsetX = 0, dragOffsetY = 0;

                            markCanvas.addEventListener('mousemove', function(e){
                                var rect = markCanvas.getBoundingClientRect();
                                var mouseX = e.clientX - rect.left, mouseY = e.clientY - rect.top;
                                var checkedValues = $('input[type="checkbox"]:checked').map(function () { return $(this).val(); }).get();
                                var hasImage = checkedValues.includes('4');
                                if (isOnResizeHandle(mouseX, mouseY, signatureCoordinates.textBox) ||
                                    isOnResizeHandle(mouseX, mouseY, signatureCoordinates.bottomBox) ||
                                    (hasImage && isOnResizeHandle(mouseX, mouseY, signatureCoordinates.imageBox))) {
                                    markCanvas.style.cursor = 'se-resize';
                                } else if (getActiveBox(mouseX, mouseY)) {
                                    markCanvas.style.cursor = 'move';
                                } else {
                                    markCanvas.style.cursor = 'default';
                                }
                            });

                            markCanvas.onmousedown = function(e){
                                var rect = markCanvas.getBoundingClientRect();
                                var mouseX = e.clientX - rect.left, mouseY = e.clientY - rect.top;
                                var checkedValues = $('input[type="checkbox"]:checked').map(function () { return $(this).val(); }).get();
                                var hasImage = checkedValues.includes('4');
                                if (isOnResizeHandle(mouseX, mouseY, signatureCoordinates.textBox)) { isResizing = true; activeBox = signatureCoordinates.textBox; }
                                else if (isOnResizeHandle(mouseX, mouseY, signatureCoordinates.bottomBox)) { isResizing = true; activeBox = signatureCoordinates.bottomBox; }
                                else if (hasImage && isOnResizeHandle(mouseX, mouseY, signatureCoordinates.imageBox)) { isResizing = true; activeBox = signatureCoordinates.imageBox; }
                                else { activeBox = getActiveBox(mouseX, mouseY); if (activeBox) { isDragging = true; dragOffsetX = mouseX - activeBox.startX; dragOffsetY = mouseY - activeBox.startY; } }
                                if (isDragging) { window.addEventListener('mousemove', onDragMove); window.addEventListener('mouseup', onDragEnd); }
                                if (isResizing) { window.addEventListener('mousemove', onResizeMove); window.addEventListener('mouseup', onResizeEnd); }
                            };

                            function onDragMove(e){
                                if (!isDragging || !activeBox) return;
                                var rect = markCanvas.getBoundingClientRect();
                                var mouseX = e.clientX - rect.left, mouseY = e.clientY - rect.top;
                                var boxW = activeBox.endX - activeBox.startX, boxH = activeBox.endY - activeBox.startY;
                                var nsx = Math.max(0, Math.min(markCanvas.width - boxW, mouseX - dragOffsetX));
                                var nsy = Math.max(0, Math.min(markCanvas.height - boxH, mouseY - dragOffsetY));
                                activeBox.startX = nsx; activeBox.startY = nsy; activeBox.endX = nsx + boxW; activeBox.endY = nsy + boxH;
                                if (activeBox.type === 'text') { $('#positionX').val(nsx); $('#positionY').val(nsy); }
                                redrawSignatureBoxes();
                            }
                            function onResizeMove(e){
                                if (!isResizing || !activeBox) return;
                                var rect = markCanvas.getBoundingClientRect();
                                var mouseX = e.clientX - rect.left, mouseY = e.clientY - rect.top;
                                var minW = 40, minH = 30;
                                var nex = Math.min(markCanvas.width, Math.max(activeBox.startX + minW, mouseX));
                                var ney = Math.min(markCanvas.height, Math.max(activeBox.startY + minH, mouseY));
                                activeBox.endX = nex; activeBox.endY = ney;
                                redrawSignatureBoxes();
                            }
                            function onDragEnd(){ isDragging = false; activeBox = null; window.removeEventListener('mousemove', onDragMove); window.removeEventListener('mouseup', onDragEnd); }
                            function onResizeEnd(){ isResizing = false; activeBox = null; window.removeEventListener('mousemove', onResizeMove); window.removeEventListener('mouseup', onResizeEnd); }

                            // First draw
                            redrawSignatureBoxes();
                        };

                        // Initialize and attach
                        try { markEventListener(); } catch(err) {}
                        var markCanvasMain = document.getElementById('mark-layer');
                        markCanvasMain.addEventListener('click', markEventListener);

                        // Insert page variant with same behavior
                        markEventListenerInsert = function(){
                            var markCanvas = document.getElementById('mark-layer-insert');
                            var markCtx = markCanvas.getContext('2d');
                            // Reuse same structure but set page to 2
                            if (!signatureCoordinates) { markEventListener(); return; }
                            $('#positionPages').val(2);
                            // Simple redraw reuse from main
                            // Draw boxes at same coordinates on insert canvas
                            markCtx.clearRect(0,0,markCanvas.width, markCanvas.height);
                            var tb = signatureCoordinates.textBox, ib = signatureCoordinates.imageBox, bb = signatureCoordinates.bottomBox;
                            var rh = 16;
                            // text (blue)
                            markCtx.beginPath(); markCtx.rect(tb.startX, tb.startY, tb.endX-tb.startX, tb.endY-tb.startY); markCtx.strokeStyle='#3b82f6'; markCtx.lineWidth=0.5; markCtx.stroke();
                            markCtx.save(); markCtx.fillStyle='#fff'; markCtx.strokeStyle='#3b82f6'; markCtx.lineWidth=2; markCtx.fillRect(tb.endX-rh, tb.endY-rh, rh, rh); markCtx.strokeRect(tb.endX-rh, tb.endY-rh, rh, rh); markCtx.restore();
                            var text = $('#modal-text').val()||''; var lines = text.split('\n'); markCtx.font='15px Sarabun'; markCtx.fillStyle='blue';
                            for (var i=0;i<lines.length;i++){ var w=markCtx.measureText(lines[i]).width; var cx=(tb.startX+tb.endX)/2 - (w/2); markCtx.fillText(lines[i], cx, tb.startY+20+(i*20)); }
                            // image (green)
                            var checkedValues = $('input[type="checkbox"]:checked').map(function(){return $(this).val();}).get();
                            if (checkedValues.includes('4')){
                                markCtx.beginPath(); markCtx.rect(ib.startX, ib.startY, ib.endX-ib.startX, ib.endY-ib.startY); markCtx.strokeStyle='#22c55e'; markCtx.lineWidth=0.5; markCtx.stroke();
                                markCtx.save(); markCtx.fillStyle='#fff'; markCtx.strokeStyle='#22c55e'; markCtx.lineWidth=2; markCtx.fillRect(ib.endX-rh, ib.endY-rh, rh, rh); markCtx.strokeRect(ib.endX-rh, ib.endY-rh, rh, rh); markCtx.restore();
                                if (signatureImgLoaded){ var iw=ib.endX-ib.startX, ih=ib.endY-ib.startY; markCtx.drawImage(signatureImg, ib.startX, ib.startY, iw, ih); imgData={x:ib.startX,y:ib.startY,width:iw,height:ih}; }
                            }
                            // bottom (purple)
                            markCtx.beginPath(); markCtx.rect(bb.startX, bb.startY, bb.endX-bb.startX, bb.endY-bb.startY); markCtx.strokeStyle='#a855f7'; markCtx.lineWidth=0.5; markCtx.stroke();
                            markCtx.save(); markCtx.fillStyle='#fff'; markCtx.strokeStyle='#a855f7'; markCtx.lineWidth=2; markCtx.fillRect(bb.endX-rh, bb.endY-rh, rh, rh); markCtx.strokeRect(bb.endX-rh, bb.endY-rh, rh, rh); markCtx.restore();
                            var i2=0; checkedValues.forEach(function(v){ if(v!=='4'){ var t=''; switch(v){case '1': t=`({{$users->fullname}})`; break; case '2': t=`{{$permission_data->permission_name}}`; break; case '3': t=`{{convertDateToThai(date("Y-m-d"))}}`; break;} if(!t)return; var ls=t.split('\n'); ls.forEach(function(line){ var w=markCtx.measureText(line).width; var cx=(bb.startX+bb.endX)/2 - (w/2); markCtx.fillText(line, cx, bb.startY + 25 + (20*i2)); i2++; }); } });
                        };
                        var markCanvasInsert = document.getElementById('mark-layer-insert');
                        markCanvasInsert.addEventListener('click', markEventListenerInsert);
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
        pdf(url);
        $('#id').val(id);
        $('#position_id').val(position_id);
        $('#positionX').val('');
        $('#positionY').val('');
        $('#txt_label').text('');
        $('#users_id').val('');
        document.getElementById('manager-save').disabled = true;
        if (status == 6) {
            $('#manager-sinature').show();
            $('#manager-save').show();
            $('#insert-pages').show();
        }
        if (status == 7) {
            $('#manager-send').show();
            $('#send-save').show();
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
        var positionX = $('#positionX').val();
        var positionY = $('#positionY').val();
        var positionPages = $('#positionPages').val();
        var pages = $('#page-select').find(":selected").val();
        var text = $('#modal-text').val();
        var checkedValues = $('input[type="checkbox"]:checked').map(function() {
            return $(this).val();
        }).get();
        // Compute sizes from 3-box layout if available; fallback to single box
        var boxW = 213, boxH = 40; // defaults
        var imageBox = null;
        var bottomBox = null;
        try {
            if (signatureCoordinates && signatureCoordinates.textBox) {
                var tb = signatureCoordinates.textBox;
                boxW = (tb.endX - tb.startX);
                boxH = (tb.endY - tb.startY);
                if (checkedValues.includes('4') && signatureCoordinates.imageBox) {
                    var ib = signatureCoordinates.imageBox;
                    imageBox = { startX: ib.startX, startY: ib.startY, width: (ib.endX - ib.startX), height: (ib.endY - ib.startY) };
                }
                if (signatureCoordinates.bottomBox) {
                    var bb = signatureCoordinates.bottomBox;
                    bottomBox = { startX: bb.startX, startY: bb.startY, width: (bb.endX - bb.startX), height: (bb.endY - bb.startY) };
                }
            } else if (typeof markCoordinates !== 'undefined' && markCoordinates) {
                var w = (markCoordinates.endX - markCoordinates.startX);
                var h = (markCoordinates.endY - markCoordinates.startY);
                if (!isNaN(w) && w > 0) boxW = w;
                if (!isNaN(h) && h > 0) boxH = h;
            }
        } catch (err) { /* keep defaults */ }
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
                            status: 7,
                            text: text,
                            checkedValues: checkedValues,
                            // Provide size hints expected by backend PDF generator
                            width: boxW,
                            height: boxH,
                            // Include image and bottom boxes (if any)
                            imageBox: imageBox,
                            bottomBox: bottomBox,
                            // Pass along position_id if present (safer for cross-position flows)
                            position_id: $('#position_id').val() || undefined
                        },
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status) {
                                Swal.fire("", "บันทึกลายเซ็นเรียบร้อยแล้ว", "success");
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
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
                        status: 8
                    },
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            if (response.status) {
                                Swal.fire("", "แทงเรื่องเรียบร้อยแล้ว", "success");
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
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
