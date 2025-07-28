function sweetAlertResponse(tipo, titulo, mensaje, actionAfter) {
    let icono = tipo;
    if(tipo=="error"){
        mensaje+=". Si el problema persiste, contacte el área de sistemas.";
    }

    Swal.fire({
        title: titulo,
        text: mensaje,
        icon: icono,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showCancelButton: false,
        showConfirmButton: true,
        confirmButtonText: 'Ok',
        confirmButtonColor: '#55AD9B',
    }).then((result) => {
        if (result.isConfirmed || result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.overlay) {
            if (actionAfter?.trim()) {
                switch (actionAfter) {
                    case "self":
                        window.location.href = window.location.href;
                    break;
                    case "trigger_dt-search":
                        setTimeout(() => {
                            $('#dt-search-0').val(' ');
                            $('#dt-search-0').trigger('keydown');
                            $('#dt-search-0').trigger('keyup');
                            $('#dt-search-0').val('');
                        }, 1000);
                    break;
                    case "none":

                    break;
                    case "exit":
                        window.close();
                    break;
                    default:
                        window.location.href = actionAfter;
                    break;
                }
            }else{
                window.location.href = window.location.href;
            }
        }
    });
}
