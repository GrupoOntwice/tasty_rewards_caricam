//SIGN UP - multiple sign up buttons

const toggleSignUp = document.querySelectorAll('.jsModalSignUp')
const closeButton = document.querySelectorAll('.jsModalSignUpClose')

toggleSignUp.forEach(function (test) {
    test.addEventListener('click', _ => {
        document.body.classList.add('modal--is-open-sign-up')
        _.stopPropagation();
    })
})

closeButton.forEach(function (test2) {
    test2.addEventListener('click', _ => {
        document.body.classList.remove('modal--is-open-sign-up')
        _.stopPropagation();
    })
})

//SIGN IN

const toggleSignIn = document.querySelector('.jsModalSignIn');
const closeSignInButton = document.querySelector('.jsModalSignInClose')

toggleSignIn.addEventListener('click', _ => {
    document.body.classList.add('modal--is-open-sign-in')
    _.stopPropagation();
})

closeSignInButton.addEventListener('click', _ => {
    document.body.classList.remove('modal--is-open-sign-in')
    _.stopPropagation();
})

//propagation to prevent the click behind the black background

const modal = document.querySelector('.modal')
modal.addEventListener('click', e => e.stopPropagation)