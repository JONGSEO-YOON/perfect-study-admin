import React from 'react'

function Demo({wire, ...props}) {

    const message = props.mingleData.message


    wire.doubleIt(2)
        .then(data => {
        })

    return (
        <div>
            {/* <!-- Create something great! --> */}
        </div>
    )
}

export default Demo
