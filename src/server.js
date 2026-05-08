import express from 'express'
import fs from 'fs'
import path from 'path'
import { randomUUID } from 'crypto'
import { app as downloaderApp } from './App.js'

const jobs = new Map()
let queue = Promise.resolve()

function now() {
    return new Date().toISOString()
}

function publicJob(job) {
    return {
        job_id: job.job_id,
        status: job.status,
        message: job.message,
        error: job.error,
        filename: job.filename,
        created_at: job.created_at,
        updated_at: job.updated_at,
        progress: job.progress,
    }
}

function addProgress(job, message) {
    job.message = message
    job.updated_at = now()
    job.progress.push(`[${job.updated_at}] ${message}`)
}

function requireApiKey(req, res, next) {
    const expected = process.env.SCRIBD_API_KEY
    if (!expected) {
        next()
        return
    }

    if (req.header('x-api-key') !== expected) {
        res.status(401).json({ error: 'Unauthorized' })
        return
    }

    next()
}

async function processJob(job) {
    job.status = 'running'
    addProgress(job, 'Processing Scribd document...')

    try {
        const result = await downloaderApp.execute(job.url)
        if (!result?.pdfPath) {
            throw new Error('Backend did not return a generated PDF path.')
        }

        const filePath = path.resolve(result.pdfPath)
        if (!fs.existsSync(filePath)) {
            throw new Error('Generated PDF file was not found.')
        }

        job.status = 'completed'
        job.file_path = filePath
        job.filename = result.filename || path.basename(filePath)
        addProgress(job, 'PDF generated successfully.')
    } catch (error) {
        job.status = 'failed'
        job.error = error?.message || 'Download failed.'
        addProgress(job, job.error)
    }
}

export function createServer() {
    const server = express()
    server.use(express.json({ limit: '1mb' }))

    server.get('/', (_req, res) => {
        res.json({ ok: true, service: 'scribd-dl', routes: ['/api/jobs'] })
    })

    server.get('/health', (_req, res) => {
        res.json({ ok: true })
    })

    server.post('/api/jobs', requireApiKey, (req, res) => {
        const url = typeof req.body?.url === 'string' ? req.body.url.trim() : ''
        if (!/^https:\/\/www\.scribd\.com\/(document|doc|presentation)\//i.test(url)) {
            res.status(400).json({ error: 'Please provide a valid Scribd document URL.' })
            return
        }

        const job = {
            job_id: randomUUID(),
            url,
            status: 'queued',
            message: 'Job queued.',
            error: null,
            filename: null,
            file_path: null,
            created_at: now(),
            updated_at: now(),
            progress: [],
        }
        addProgress(job, 'Job queued.')
        jobs.set(job.job_id, job)

        queue = queue
            .then(() => processJob(job))
            .catch((error) => {
                job.status = 'failed'
                job.error = error?.message || 'Queue failed.'
                addProgress(job, job.error)
            })

        res.status(202).json(publicJob(job))
    })

    server.get('/api/jobs/:jobId', requireApiKey, (req, res) => {
        const job = jobs.get(req.params.jobId)
        if (!job) {
            res.status(404).json({ error: 'Job not found' })
            return
        }

        res.json(publicJob(job))
    })

    server.get('/api/jobs/:jobId/file', requireApiKey, (req, res) => {
        const job = jobs.get(req.params.jobId)
        if (!job) {
            res.status(404).json({ error: 'Job not found' })
            return
        }

        if (job.status !== 'completed' || !job.file_path || !fs.existsSync(job.file_path)) {
            res.status(409).json({ error: 'PDF is not ready yet.' })
            return
        }

        res.download(job.file_path, job.filename || path.basename(job.file_path))
    })

    return server
}

export function startServer(port = process.env.PORT || 3000) {
    const server = createServer()
    server.listen(port, () => {
        console.log(`Scribd backend listening on port ${port}`)
    })
}
