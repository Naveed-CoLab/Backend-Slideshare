import path from 'path'
import puppeteer from 'puppeteer-extra'
import StealthPlugin from 'puppeteer-extra-plugin-stealth'
import { directoryIo } from "../io/DirectoryIo.js";

puppeteer.use(StealthPlugin())

// Realistic desktop user agents
const USER_AGENTS = [
  'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
  'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
  'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
  'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
]

const randomBetween = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms))

class PuppeteerSg {
  static buffer = 1000;

  constructor() {
    if (!PuppeteerSg.instance) {
      PuppeteerSg.instance = this;
      process.on('exit', () => {
        this.close();
      });
    }
    return PuppeteerSg.instance;
  }

  /**
   * Launch a browser with stealth settings to appear human
   */
  async launch() {
    const isCI = process.env.CI === 'true';
    const args = [
      '--disable-blink-features=AutomationControlled',
      '--disable-infobars',
      '--window-size=1366,768',
      '--start-maximized',
    ];
    if (isCI) {
      args.push('--no-sandbox', '--disable-setuid-sandbox');
    }
    this.browser = await puppeteer.launch({
      headless: "new",
      defaultViewport: { width: 1366, height: 768 },
      args,
      timeout: 0,
    });
  }

  /**
   * Simulate human-like mouse movement across the page
   */
  async humanMouseMove(page) {
    const steps = randomBetween(3, 7);
    for (let i = 0; i < steps; i++) {
      await page.mouse.move(
        randomBetween(100, 1200),
        randomBetween(100, 600),
        { steps: randomBetween(5, 15) }
      );
      await sleep(randomBetween(50, 200));
    }
  }

  /**
   * New a page with human-like behaviour
   */
  async getPage(url) {
    if (!this.browser) {
      await this.launch()
    }
    let page = await this.browser.newPage()

    // Set a random realistic user agent
    const ua = USER_AGENTS[randomBetween(0, USER_AGENTS.length - 1)]
    await page.setUserAgent(ua)

    // Set realistic HTTP headers
    await page.setExtraHTTPHeaders({
      'Accept-Language': 'en-US,en;q=0.9',
      'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
      'sec-ch-ua': '"Chromium";v="124", "Google Chrome";v="124", "Not-A.Brand";v="99"',
      'sec-ch-ua-mobile': '?0',
      'sec-ch-ua-platform': '"Windows"',
    })

    await page.goto(url, {
      waitUntil: "load",
    })

    // Human-like pause after page load
    await sleep(randomBetween(800, 1800))

    // Simulate human mouse movement
    await this.humanMouseMove(page)

    // Another small pause before interacting
    await sleep(randomBetween(300, 700))

    await this.injectHelperFunctions(page)
    await sleep(this.buffer)
    return page
  }

  /**
   * Generate PDF from the page
   */
  async generatePDF(page, pdfPath, options = {}) {
    await directoryIo.create(path.dirname(pdfPath));
    await page.pdf({
      path: pdfPath,
      printBackground: true,
      timeout: 0,
      ...options
    });
  }

  /**
   * Close the browser
   */
  async close() {
    if (this.browser) {
      await this.browser.close();
      this.browser = null;
    }
  }

  /**
   * Inject helper functions into the page context
   */
  async injectHelperFunctions(page) {
    const browserHelpers = `
      window.__helpers__ = {
        lazyLoad: async (selector = null, rendertime = 100) => {
          await new Promise(resolve => {
            const container = selector ? document.querySelector(selector) : null;
            if (selector && !container) {
              return resolve();
            }
            let prevScroll = 0;
            const timer = setInterval(() => {
              if (container) {
                container.scrollTop += container.clientHeight;
                if (container.scrollTop === prevScroll) {
                  clearInterval(timer);
                  resolve();
                }
                prevScroll = container.scrollTop;
                if (container.scrollTop + container.clientHeight >= container.scrollHeight) {
                  clearInterval(timer);
                  resolve();
                }
              } else {
                const scrollHeight = document.body.scrollHeight;
                window.scrollBy(0, window.innerHeight * 0.8);
                if (window.innerHeight + window.scrollY >= scrollHeight) {
                  clearInterval(timer);
                  resolve();
                }
              }
            }, rendertime);
          });
        },
        hideSelectorAll: (selector) => {
          document.querySelectorAll(selector).forEach(el => el.style.display = 'none');
        },
        showSelectorAll: (selector) => {
          document.querySelectorAll(selector).forEach(el => el.style.display = 'block');
        },
        removeSelectorAll: (selector) => {
          document.querySelectorAll(selector).forEach(el => el.remove());
        },
        removeMarginSelectorAll: (selector) => {
          document.querySelectorAll(selector).forEach(el => el.style.margin = '0');
        },
        timeout: (ms) => new Promise(resolve => setTimeout(resolve, ms)),
      };
    `;
    await page.evaluate(browserHelpers);
  }
}

export const puppeteerSg = new PuppeteerSg()
