

// const accordions = Array.from(document.querySelectorAll('.accordion'))

// accordions.forEach(accordion => {
//     const accordionHeader = accordion.querySelector('.accordion__header')

//     accordionHeader.addEventListener('click', e => {

//         accordion.classList.add('is-open')

//         if (accordion.classList.contains("is-open")) {
//             accordion.classList.remove('is-open')
//         }
//     })
// })

const accordions = Array.from(document.querySelectorAll('.accordion'))

accordions.forEach(accordion => {
    const accordionHeader = accordion.querySelector('.accordion__header')

    accordionHeader.addEventListener('click', e => {

        accordions.forEach(accordionItem => {
            accordionItem.classList.remove('is-open');
        });

        accordion.classList.add('is-open')
        //accordion.classList.remove('is-open')
    })
})

// const accordions = Array.from(document.querySelectorAll('.accordion'))

// accordions.forEach(accordion => {
//     const accordionHeader = accordion.querySelector('.accordion__header')

//     accordion.classList.remove('is-open');

//     accordionHeader.addEventListener('click', e => {

//         accordion.classList.add('is-open');

//     })
// })



