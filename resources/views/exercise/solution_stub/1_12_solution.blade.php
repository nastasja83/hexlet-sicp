(define (solution row col)
  (cond (( = col 1) 1)
        (( = col row) 1)
        (( > col row) 0)
        ((+
         (solution (- row 1) (- col 1))
         (solution (- row 1) col)
         ))
  )
)