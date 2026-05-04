<script>
    (() => {
        if (window.VozECifraChord) {
            return;
        }

        const NOTE_TO_SEMITONE = {
            'C': 0,
            'B#': 0,
            'C#': 1,
            'Db': 1,
            'D': 2,
            'D#': 3,
            'Eb': 3,
            'E': 4,
            'Fb': 4,
            'E#': 5,
            'F': 5,
            'F#': 6,
            'Gb': 6,
            'G': 7,
            'G#': 8,
            'Ab': 8,
            'A': 9,
            'A#': 10,
            'Bb': 10,
            'B': 11,
            'Cb': 11,
        };

        const SEMITONE_TO_SHARP = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
        const SEMITONE_TO_FLAT = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];
        const CHORD_REGEX = /^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\+|-|[0-9#b])|\([^\)\]]+\))*(?:\/[A-G](?:#|b)?)?$/;
        const CHORD_PARTS_REGEX = /^([A-G](?:#|b)?)(.*?)(?:\/([A-G](?:#|b)?))?$/;

        const normalizeWhitespace = (value) => (value || '').replace(/\\n/g, '\n').replace(/\r\n/g, '\n').replace(/\r/g, '\n').replace(/\n{3,}/g, '\n\n');

        const escapeHtml = (value) => String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const normalizeSectionLabel = (value) => String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();

        const isSectionLabel = (value) => {
            const label = normalizeSectionLabel(value);
            return label.length <= 32 && /^(refrao:?|refr\.?|ref:|entrada|final|ponte|estrofe|verso)(?:\s|$)/.test(label);
        };

        const sectionLabelClass = (value) => {
            return /^(refrao:?|refr\.?|ref:)(?:\s|$)/.test(normalizeSectionLabel(value))
                ? 'cifra-marcacao cifra-marcacao--refrao'
                : 'cifra-marcacao';
        };

        const isChord = (value) => {
            const chord = String(value || '').trim();
            return chord !== '' && !chord.includes(' ') && CHORD_REGEX.test(chord);
        };

        const getSemitone = (note) => {
            const normalized = String(note || '').trim();
            return Object.prototype.hasOwnProperty.call(NOTE_TO_SEMITONE, normalized)
                ? NOTE_TO_SEMITONE[normalized]
                : null;
        };

        const parseChord = (value) => {
            const chord = String(value || '').trim();
            if (!isChord(chord)) {
                return null;
            }

            const match = chord.match(CHORD_PARTS_REGEX);
            if (!match) {
                return null;
            }

            return {
                root: match[1],
                suffix: match[2] || '',
                bass: match[3] || null,
            };
        };

        const buildSignatureFromParts = (root, suffix, bass) => {
            const rootSemitone = getSemitone(root);
            if (rootSemitone === null) {
                return null;
            }

            const bassSemitone = bass ? getSemitone(bass) : null;
            if (bass && bassSemitone === null) {
                return null;
            }

            return [rootSemitone, suffix || '', bassSemitone === null ? '' : bassSemitone].join('|');
        };

        const getChordSignature = (value) => {
            const parsed = parseChord(value);
            if (!parsed) {
                return null;
            }

            return buildSignatureFromParts(parsed.root, parsed.suffix, parsed.bass);
        };

        const chooseDisplayNote = (semitone, preferFlats) => {
            const scale = preferFlats ? SEMITONE_TO_FLAT : SEMITONE_TO_SHARP;
            return scale[((semitone % 12) + 12) % 12];
        };

        const transposeNote = (note, steps, preferFlats = false) => {
            const semitone = getSemitone(note);
            if (semitone === null) {
                return note;
            }

            return chooseDisplayNote(semitone + steps, preferFlats);
        };

        const transposeChord = (value, steps) => {
            const parsed = parseChord(value);
            if (!parsed || Number(steps || 0) === 0) {
                return String(value || '');
            }

            const rootPrefersFlats = parsed.root.includes('b');
            const bassPrefersFlats = parsed.bass ? parsed.bass.includes('b') : false;

            return [
                transposeNote(parsed.root, steps, rootPrefersFlats),
                parsed.suffix,
                parsed.bass ? '/' + transposeNote(parsed.bass, steps, bassPrefersFlats) : '',
            ].join('');
        };

        const transposeBracketedText = (text, steps) => {
            const amount = Number(steps || 0);
            if (amount === 0) {
                return normalizeWhitespace(text);
            }

            return normalizeWhitespace(text).replace(/\[([^\[\]\r\n]+)\]/g, (match, possibleChord) => {
                const chord = String(possibleChord || '').trim();
                return isChord(chord) ? `[${transposeChord(chord, amount)}]` : match;
            });
        };

        const stripBracketedChords = (text) => normalizeWhitespace(text).replace(/\[([^\[\]\r\n]+)\]/g, (match, possibleChord) => {
            return isChord(possibleChord) ? '' : match;
        });

        const renderChordSheetHtml = (text, options = {}) => {
            const attributeName = options.chordAttribute || 'data-acorde-hover';
            const lines = normalizeWhitespace(text).split('\n');

            return lines.map((line) => {
                const trimmed = line.trim();

                if (trimmed === '') {
                    return '<div class="h-4"></div>';
                }

                const labelMatch = trimmed.match(/^\[(.+)\]$/u);
                if (labelMatch && !isChord(labelMatch[1])) {
                    return `<div class="${sectionLabelClass(labelMatch[1])}">${escapeHtml(labelMatch[1])}</div>`;
                }

                if (isSectionLabel(trimmed)) {
                    return `<div class="${sectionLabelClass(trimmed)}">${escapeHtml(trimmed)}</div>`;
                }

                const regex = /\[([^\[\]\r\n]+)\]/g;
                let lastIndex = 0;
                let pendingChords = [];
                let segments = '';
                let match;

                while ((match = regex.exec(line)) !== null) {
                    const textBefore = line.slice(lastIndex, match.index);

                    if (textBefore !== '') {
                        const chordsHtml = pendingChords
                            .map((chord) => `<span class="cifra-acorde" ${attributeName}="${escapeHtml(chord)}">${escapeHtml(chord)}</span>`)
                            .join(' ');

                        segments += `<span class="cifra-segmento"><span class="cifra-acordes">${chordsHtml}</span><span class="cifra-letra">${escapeHtml(textBefore)}</span></span>`;
                        pendingChords = [];
                    }

                    if (isChord(match[1])) {
                        pendingChords.push(String(match[1]).trim());
                    } else {
                        segments += `<span class="cifra-segmento"><span class="cifra-acordes"></span><span class="cifra-letra">${escapeHtml(match[0])}</span></span>`;
                    }

                    lastIndex = regex.lastIndex;
                }

                const textAfter = line.slice(lastIndex);
                if (textAfter !== '' || pendingChords.length > 0) {
                    const chordsHtml = pendingChords
                        .map((chord) => `<span class="cifra-acorde" ${attributeName}="${escapeHtml(chord)}">${escapeHtml(chord)}</span>`)
                        .join(' ');

                    segments += `<span class="cifra-segmento"><span class="cifra-acordes">${chordsHtml}</span><span class="cifra-letra">${escapeHtml(textAfter || ' ')}</span></span>`;
                }

                return `<div class="cifra-linha">${segments}</div>`;
            }).join('');
        };

        const buildChordGroups = (chords) => (Array.isArray(chords) ? chords : []).reduce((groups, chord) => {
            const name = String(chord?.nome || '').trim();
            if (!name) {
                return groups;
            }

            const signature = getChordSignature(name);
            if (!groups.byName[name]) {
                groups.byName[name] = [];
            }

            groups.byName[name].push(chord);

            if (signature) {
                if (!groups.bySignature[signature]) {
                    groups.bySignature[signature] = [];
                }

                groups.bySignature[signature].push(chord);
            }

            return groups;
        }, { byName: {}, bySignature: {} });

        const getChordMatches = (groups, chordName) => {
            const name = String(chordName || '').trim();
            if (!name) {
                return [];
            }

            if (groups?.byName?.[name]) {
                return groups.byName[name];
            }

            const signature = getChordSignature(name);
            if (signature && groups?.bySignature?.[signature]) {
                return groups.bySignature[signature];
            }

            return [];
        };

        const extractChordsFromBracketedText = (text) => {
            const matches = normalizeWhitespace(text).match(/\[([^\[\]\r\n]+)\]/g) || [];

            return [...new Set(matches
                .map((match) => match.slice(1, -1).trim())
                .filter((value) => isChord(value)))];
        };

        window.VozECifraChord = {
            escapeHtml,
            isChord,
            parseChord,
            transposeNote,
            transposeChord,
            transposeBracketedText,
            stripBracketedChords,
            renderChordSheetHtml,
            getChordSignature,
            isSectionLabel,
            sectionLabelClass,
            buildChordGroups,
            getChordMatches,
            extractChordsFromBracketedText,
        };
    })();
</script>
