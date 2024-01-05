class Approve {
    /**
     * @param {HTMLCollectionOf<HTMLTableRowElement>} requests
     */
    constructor(requests) {
        this.requests = requests
    }

    activate() {
        this.requests.forEach(request => {
            let node = document.createElement('a')
            node.className = 'button button--primary'
            node.innerHTML = 'Schválit'
            node.setAttribute('reqid', request.getAttribute('reqid'))
            request.removeAttribute('reqid')
            node.addEventListener('click', event => this.linkClick(event))
            request.lastElementChild.appendChild(node)
        })
    }

    linkClick(event) {
        event.preventDefault()
        let target = event.target

        fetch(`/api/requests/${target.getAttribute('reqid')}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'json'
            },
            body: {}
        })
            .then(response => target.parentNode.previousElementSibling.innerHTML = 'Schváleno')
    }
}