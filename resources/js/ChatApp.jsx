import React from 'react'

function Demo({wire, ...props}) {

    const message = props.mingleData.message

    console.log(message) // 'Message in a bottle 🍾'

    wire.doubleIt(2)
        .then(data => {
            console.log(data) // 4
        })

    return (
        <div>
            {/* <!-- Create something great! --> */}
        </div>
    )
}

export default Demo
