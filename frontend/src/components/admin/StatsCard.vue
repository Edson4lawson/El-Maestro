<template>
  <div class="kpi-card" :class="`kpi-card--${color}`" @mousemove="onMouseMove" @mouseleave="onMouseLeave">
    <!-- Ambient Reactive Spotlight Glow -->
    <div 
      class="kpi-card__spotlight" 
      :style="{
        background: `radial-gradient(400px circle at ${mousePos.x}px ${mousePos.y}px, ${sparkHex}22, transparent 70%)`
      }"
      aria-hidden="true"
    ></div>

    <!-- Top Row: Icon + Trend / Status Chip -->
    <div class="kpi-card__header">
      <div class="kpi-card__icon-box" :class="`kpi-card__icon-box--${color}`">
        <component :is="ico" v-if="ico" :size="20" stroke-width="2.2" />
        <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
        </svg>
      </div>

      <div class="kpi-card__trend-badge" :class="`kpi-card__trend-badge--${trend}`">
        <svg v-if="trend === 'up'" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
          <polyline points="17 6 23 6 23 12"></polyline>
        </svg>
        <svg v-else-if="trend === 'down'" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
          <polyline points="17 18 23 18 23 12"></polyline>
        </svg>
        <span class="kpi-card__trend-value">{{ trendValue }}</span>
      </div>
    </div>

    <!-- Metric Body -->
    <div class="kpi-card__body">
      <p class="kpi-card__title">{{ title }}</p>
      <div class="kpi-card__value-row">
        <h3 class="kpi-card__value">{{ display }}</h3>
        <span v-if="suffix" class="kpi-card__suffix">{{ suffix }}</span>
      </div>
    </div>

    <!-- Footer: Description + Sparkline -->
    <div class="kpi-card__footer">
      <div class="kpi-card__meta">
        <span class="kpi-card__desc">{{ description }}</span>
        <span v-if="subBadge" class="kpi-card__sub-badge">{{ subBadge }}</span>
      </div>

      <!-- High Precision Vector Sparkline -->
      <div v-if="sparklineData.length > 1" class="kpi-card__sparkline-wrap">
        <svg class="kpi-card__sparkline" viewBox="0 0 100 36" preserveAspectRatio="none">
          <defs>
            <linearGradient :id="`sg-${uid}`" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" :stop-color="sparkHex" stop-opacity="0.45" />
              <stop offset="100%" :stop-color="sparkHex" stop-opacity="0.0" />
            </linearGradient>
          </defs>
          <path :d="areaPath" :fill="`url(#sg-${uid})`" />
          <path :d="linePath" fill="none" :stroke="sparkHex" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
          <!-- Peak Point Pulse -->
          <circle v-if="peakPoint" :cx="peakPoint.x" :cy="peakPoint.y" r="3" :fill="sparkHex" class="kpi-card__peak-point" />
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive } from 'vue'
import * as Icons from 'lucide-vue-next'

let _instanceCount = 0
const uid = ++_instanceCount

const props = defineProps({
  title:        { type: String, required: true },
  value:        { type: [String, Number], required: true },
  suffix:       { type: String, default: '' },
  icon:         { type: String, default: 'TrendingUp' },
  color:        { type: String, default: 'gold' },
  trend:        { type: String, default: 'up' },
  trendValue:   { type: String, default: '—' },
  description:  { type: String, default: '' },
  subBadge:     { type: String, default: '' },
  sparklineData:{ type: Array, default: () => [] },
})

const ico = computed(() => Icons[props.icon] || Icons.TrendingUp)

const mousePos = reactive({ x: 120, y: 80 })
const onMouseMove = (e) => {
  const rect = e.currentTarget.getBoundingClientRect()
  mousePos.x = e.clientX - rect.left
  mousePos.y = e.clientY - rect.top
}
const onMouseLeave = () => {
  mousePos.x = -100
  mousePos.y = -100
}

const display = computed(() => {
  if (typeof props.value === 'number') {
    return props.value.toLocaleString('fr-FR')
  }
  return props.value
})

const colorMap = {
  gold:   '#D4AF37',
  blue:   '#3B82F6',
  green:  '#10B981',
  purple: '#A855F7',
  red:    '#EF4444',
  orange: '#F59E0B'
}
const sparkHex = computed(() => colorMap[props.color] || '#D4AF37')

// High-Fidelity SVG Curve calculation
const W = 100
const H = 36
const P = 3

const points = computed(() => {
  const data = props.sparklineData
  if (data.length < 2) return []
  const max = Math.max(...data)
  const min = Math.min(...data)
  const range = max - min || 1

  return data.map((val, idx) => {
    const x = (idx / (data.length - 1)) * (W - P * 2) + P
    const y = H - P - ((val - min) / range) * (H - P * 2)
    return { x, y, val }
  })
})

const linePath = computed(() => {
  const pts = points.value
  if (pts.length < 2) return ''
  return pts.reduce((acc, pt, i) => {
    if (i === 0) return `M ${pt.x} ${pt.y}`
    return `${acc} L ${pt.x} ${pt.y}`
  }, '')
})

const areaPath = computed(() => {
  const pts = points.value
  if (pts.length < 2) return ''
  const first = pts[0]
  const last = pts[pts.length - 1]
  return `${linePath.value} L ${last.x} ${H} L ${first.x} ${H} Z`
})

const peakPoint = computed(() => {
  const pts = points.value
  if (!pts.length) return null
  return pts.reduce((prev, curr) => (curr.val > prev.val ? curr : prev), pts[0])
})
</script>

<style scoped>
@import '../../styles/admin-tokens.css';

.kpi-card {
  position: relative;
  overflow: hidden;
  background: rgba(19, 19, 23, 0.7);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: var(--r-xl);
  padding: 1.35rem 1.4rem 1.15rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 160px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.45);
  transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
  cursor: default;
}

.kpi-card:hover {
  transform: translateY(-3px);
  border-color: rgba(255, 255, 255, 0.18);
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.65), 0 0 24px rgba(212, 175, 55, 0.12);
}

.kpi-card__spotlight {
  position: absolute;
  inset: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
  z-index: 0;
}

/* Header */
.kpi-card__header {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.kpi-card__icon-box {
  width: 42px;
  height: 42px;
  border-radius: var(--r-md);
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.25s ease;
}

.kpi-card__icon-box--gold   { color: #F3E5AB; background: rgba(212, 175, 55, 0.1); border-color: rgba(212, 175, 55, 0.3); }
.kpi-card__icon-box--blue   { color: #93C5FD; background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.3); }
.kpi-card__icon-box--green  { color: #6EE7B7; background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.3); }
.kpi-card__icon-box--purple { color: #C4B5FD; background: rgba(168, 85, 247, 0.1); border-color: rgba(168, 85, 247, 0.3); }
.kpi-card__icon-box--orange { color: #FCD34D; background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.3); }

.kpi-card:hover .kpi-card__icon-box {
  transform: scale(1.06);
}

.kpi-card__trend-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 9px;
  border-radius: var(--r-full);
  font-size: 11px;
  font-weight: 600;
  font-family: var(--font-sans);
  letter-spacing: -0.01em;
}

.kpi-card__trend-badge--up {
  color: #34D399;
  background: rgba(16, 185, 129, 0.12);
  border: 1px solid rgba(16, 185, 129, 0.25);
}

.kpi-card__trend-badge--down {
  color: #F87171;
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.25);
}

.kpi-card__trend-badge--neutral {
  color: var(--text-2);
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Body */
.kpi-card__body {
  position: relative;
  z-index: 1;
  margin: 0.9rem 0 0.4rem;
}

.kpi-card__title {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-3);
  letter-spacing: 0.04em;
  text-transform: uppercase;
  margin-bottom: 4px;
}

.kpi-card__value-row {
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.kpi-card__value {
  font-size: 30px;
  font-weight: 800;
  color: var(--text);
  letter-spacing: -0.03em;
  line-height: 1.1;
  font-variant-numeric: tabular-nums;
}

.kpi-card__suffix {
  font-size: 14px;
  font-weight: 600;
  color: var(--gold);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Footer */
.kpi-card__footer {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  padding-top: 0.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
}

.kpi-card__meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.kpi-card__desc {
  font-size: 11px;
  color: var(--text-3);
  font-weight: 500;
}

.kpi-card__sub-badge {
  font-size: 10px;
  font-weight: 600;
  color: var(--gold);
  letter-spacing: 0.03em;
}

.kpi-card__sparkline-wrap {
  width: 80px;
  height: 34px;
  flex-shrink: 0;
}

.kpi-card__sparkline {
  width: 100%;
  height: 100%;
  overflow: visible;
}

.kpi-card__peak-point {
  filter: drop-shadow(0 0 4px currentColor);
}
</style>
