var workingCaption = '<option value="0">...</option>';

var filialSelect = $('#id_filial');

var groupSelect = $('#id_group')
groupSelect.defHTML = groupSelect.html();

var fioSelect = $('#id_fio')
fioSelect.defHTML = fioSelect.html();
$('#fitem_id_manualfio').hide()

function linkSelects(masterSelect, slaveSelect, actionFn) {

        masterSelect.change(function (ev) {
            slaveSelect.prop("disabled", true);
            slaveSelect.html(workingCaption);
            slaveSelect.change();

            if (ev.target.value == 0) {
                slaveSelect.html(slaveSelect.defHTML);
                return;
            }

            $.getJSON(
                "/login/abit_info.php",
                actionFn(ev.target.value),
                function (out) {
                    var newOptions = [];

                    $.each(out.output, function (k,item) {
                            newOptions.push([item.name, item.id]);
                    })
                    newOptions.sort();
                    var optionsHTML , i;
                    for (i = 0 ; i < newOptions.length; i++) {
                        optionsHTML += '<option value="'+newOptions[i][1]+'">'+newOptions[i][0]+'</option>'
                    }
                    if (newOptions.length !== 1) {
                        slaveSelect.html(slaveSelect.defHTML+optionsHTML);
                        slaveSelect.prop("disabled", false);
                    } else {
                        slaveSelect.html(optionsHTML);
                        slaveSelect.change();
                    }
                });
        })
}

linkSelects(filialSelect,       groupSelect,    function (id) { return {action: 'listgroupss', filial:id} });
linkSelects(groupSelect,        fioSelect,   function (id) { return {action: 'listfios', group:id} });

function translit (str) {
    var L = {
        'А':'A','а':'a','Б':'B','б':'b','В':'V','в':'v','Г':'G','г':'g',
        'Д':'D','д':'d','Е':'E','е':'e','Ё':'Yo','ё':'yo','Ж':'Zh','ж':'zh',
        'З':'Z','з':'z','И':'I','и':'i','Й':'Y','й':'y','К':'K','к':'k',
        'Л':'L','л':'l','М':'M','м':'m','Н':'N','н':'n','О':'O','о':'o',
        'П':'P','п':'p','Р':'R','р':'r','С':'S','с':'s','Т':'T','т':'t',
        'У':'U','у':'u','Ф':'F','ф':'f','Х':'Kh','х':'kh','Ц':'Ts','ц':'ts',
        'Ч':'Ch','ч':'ch','Ш':'Sh','ш':'sh','Щ':'Sch','щ':'sch','Ъ':'"','ъ':'"',
        'Ы':'Y','ы':'y','Ь':"'",'ь':"'",'Э':'E','э':'e','Ю':'Yu','ю':'yu',
        'Я':'Ya','я':'ya'
    }, r = '', k;

    for (k in L) 
        r += k;
    r = new RegExp('[' + r + ']', 'g');
    k = function(a){ return a in L ? L[a] : '';  };
    return str.replace(r, k);
}

var autoName = '';

fioSelect.change(function (ev) {
    
    if (ev.target.value == -1) {
        $('#fitem_id_manualfio').show()
        $('#fitem_id_manualfio').focus()
    }
    else
        $('#fitem_id_manualfio').hide()

    if (ev.target.value == 0 || ev.target.value == -1)
        return;

    var fio_raw = $("#id_fio option:selected").text().split(/\s+/).toLowerCase();
    var newAutoName = translit(fio_raw[0]+fio_raw[1][0]+fio_raw[2][0]);
    if ($('#id_username').val() == autoName) {
        $('#id_username').val(newAutoName);
        autoName = newAutoName;
    }
});

$('form').submit(function () {
    $('form select').prop('disabled', false);
})

filialSelect.change();
