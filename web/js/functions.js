$(function () {

    console.log($('#photo').length);
    // verifier la présence de l'input dont l'id est photo
    if ($('#photo').length > 0) {

        $('#photo').on('change', function (e) {
            console.log('Je change de photo !');
            let fichier = e.target.files;
            console.log(fichier);
            if (fichier.length > 0) {

                $('#boxphoto').html('');

                for (i = 0; i < fichier.length; i++) {

                    // controle taille
                    if (fichier[i].size > 0 && fichier[i].size < 2.048e6) {
                        let ext = ['image/jpeg', 'image/png', 'image/gif'];
                        // controle format de fichier
                        if ($.inArray(fichier[i].type, ext) != -1) {                            
                            // Ok bon format bonne taille                            
                                let reader = new FileReader(); 
                                reader._NAME = fichier[i].name;
                                reader.readAsDataURL(fichier[i]);
                                reader.onload = (e) => {
                                    $('#boxphoto')
                                        .removeClass('alert alert-danger')
                                        .append('<img src="' + e.target.result + '" alt="' + e.target._NAME + '" class="img-fluid w-25">');
                                } 

                        } else {
                            $("#boxphoto")
                                .addClass('alert alert-danger')
                                .append("Format attendu : JPEG, PNG, GIF :" + fichier[i].name + '<br>');
                            $('#photo').val('');
                        }
                    }
                    else {
                        $("#boxphoto")
                            .addClass('alert alert-danger')
                            .append("Taille jusqu'à 2Mo : " + fichier[i].name + '<br>');
                        $('#photo').val('');
                    }
                }
            }
        });
    }

    if($('.confirm').length > 0){
        
        $('.confirm').on('click',function(){
            return (confirm('Etes-vous sûr(e) de vouloir supprimer ce produit ?'));
        });
    }

    // passer les images vignette dans la mainimg

    if($('.vignette').length>0){
        $('.vignette').on('click',function(){
            source = $(this).attr('src');
            dest = $('.mainimg').attr('src');
            // switch
            $('.mainimg').attr('src',source);
            $(this).attr('src',dest);
        });
    }


}); // FIN DU Document Ready