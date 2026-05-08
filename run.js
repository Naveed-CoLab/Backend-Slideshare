import { app } from './src/App.js'
import { startServer } from './src/server.js'
import readline from 'readline'

function askUrlFromPrompt() {
    const rl = readline.createInterface({
        input: process.stdin,
        output: process.stdout,
    })

    return new Promise((resolve) => {
        rl.question('Enter document URL: ', (answer) => {
            rl.close()
            resolve(answer.trim())
        })
    })
}

let url = process.argv[2]

if (url === '--server' || process.env.PORT || !process.stdin.isTTY) {
    startServer()
} else {
    if (!url) {
        url = await askUrlFromPrompt()
    }

    if (!url) {
        console.error('No URL provided.')
        process.exit(1)
    }

    try {
        await app.execute(url)
    } catch (error) {
        console.error(error?.message ?? error)
        process.exit(1)
    }
}