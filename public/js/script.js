/**
 * Created by SantiKush on 27/12/2018.
 */

function imgHoverFue(){
    var jaquette = document.getElementById("iVisibilityFue");
    if (jaquette.style.visibility === "hidden"){
        jaquette.style.visibility = "visible";
    }else if (jaquette.style.visibility === "visible"){
        jaquette.style.visibility = "hidden";
    }
    console.log("yo");
};

function imgHoverSanti(){
    var jaquette = document.getElementById("iVisibilitySanti");
    if (jaquette.style.visibility === "hidden"){
        jaquette.style.visibility = "visible";
    }else if (jaquette.style.visibility === "visible"){
        jaquette.style.visibility = "hidden";
    }
};

$('input[type="file"]').change(function(e){
    var fileName = e.target.files[0].name;
    $('.custom-file-label').html(fileName);
});

function like(i){
    id = "like"+i;
    var like = document.getElementById(id).style;

    if(like.color == "green"){
        like.color = "black";
    }else {
        like.color = "green";
    }
}

function showComment(){
    console.log("yo");
    var comments = document.getElementById('comments').style;
    if(comments.visibility == "hidden"){
        comments.visibility = "visible"
    }else{
        comments.visibility = "hidden"
    }
}