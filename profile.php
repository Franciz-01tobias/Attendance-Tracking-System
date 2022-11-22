<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/profile.css">
    <title>Upload Image</title>
</head>
<body>
    <div class="container">
        <h1>Profile Image</h1>
        <div class="avatar-upload">
            <div class="avatar-edit">
                <input type="file" name="image" id="imageUpload" accept=".png, .jpg, .jpeg" />
                <label for="ImageUpload"></label>
            </div>

            <div class="avatar-preview">
                <div id="imagePreview" style="background-image:url('../images/stu.jpeg');">
                </div>
            </div>
        </div>
    </div>
    <script src="">
        function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreview').css('background-image', 'url('+e.target.result +')');
            $('#imagePreview').hide();
            $('#imagePreview').fadeIn(650);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
$("#imageUpload").change(function() {
    readURL(this);
});
    </script>
</body>
</html>