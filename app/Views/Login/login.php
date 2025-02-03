<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso</title>
    <link rel="stylesheet" href="assets\css\login.css">
    <script src="https://kit.fontawesome.com/a25933befb.js" crossorigin="anonymous"></script>   
</head>
<body>
<div class="containerLogin">

    <?php  
    $session = session();
    $id=$session->get('id');
    if(!$id){  ?>
    <div class="form-content">
            <h1 id="title">
                Ingreso
            </h1>
            <?php if(session()->getFlashdata('msg')):?>
                    <div class="alert alert-danger"><?= session()->getFlashdata('msg') ?></div>
                <?php endif;?>
            <form action="<?php echo base_url('enviarlogin');?>" method="post">
                <div class="input-group">
                    
                    <div class="input-field">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="text" placeholder="correo" name= "email">
                    </div>
                    <div class="input-field">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" placeholder="contraseña" name="pass">
                    </div>
                    
                </div>
                <div class="">
                    <!-- Boton de ingresar -->
                    <button type="submit" class="button2"> Ingresar </button>
                </div>
            </form>
            </div>
    
    <?php    }else{  ?>
        <h1 class="titulo-vidrio">Usted ya esta Logueado.!</h1>
        <?php  } ?>
        
        </div>
</body>
</html>
