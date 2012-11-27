\documentclass[a4paper,8pt]{article}
\usepackage{geometry} % paper=a4paper
\usepackage[english]{babel}
\usepackage[utf8]{inputenc}
\usepackage{units}
\usepackage{graphicx}
\usepackage{fancyhdr}
\usepackage{fp}
\usepackage[table]{xcolor}
\usepackage{tikz}
\usepackage{array}
\usepackage{multicol}
\usepackage{textcomp}
\usepackage{marvosym}
\usepackage{lastpage}


\usetikzlibrary{fit}

\renewcommand\sfdefault{phv}
\renewcommand{\familydefault}{\sfdefault}


\setlength{\oddsidemargin}{-2cm}
\setlength{\evensidemargin}{-2cm}
\setlength{\textwidth}{19cm}
\setlength{\headheight}{1cm}
\setlength{\topmargin}{-3.5cm}
\addtolength{\textheight}{29.9cm} 

 
\def\DSDEADLINEDATE{31 ao\^{u}t 2012}	
\def\DSSTOCKSDATE{<?php echo strtoupper($ds->date_stock); ?>}		
\def\DSHEADTITRE{\textsl{\textbf{DECLARATION DE STOCKS DE VIN}}}       
\def\DSHEADTEXTE{Cet imprimé doit \^{e}tre obligatoirement rempli \textsl{\textbf{avant le <?php echo $ds->date_echeance; ?>}} au plus tard \\par tous les propriétaires, fermiers, métayers, groupements de producteurs,\\négociants détenant des stocks de vins d'appellation d'origine (revendiqués et/ou\\agrées) et quels que soient leurs lieux d'entreposage selon la liste proposée\\ ci-après conformément à l'Accord Interprofessionnel d'InterLoire en vigueur.}
\def\DSNUMERO{<?php echo $ds->_id; ?>}

\def\DSClientNUM{<?php echo $ds->identifiant; ?>}
\def\DSClientCVI{<?php echo $ds->declarant->cvi; ?>}
\def\DSClientNom{<?php echo ($ds->declarant->raison_sociale)? $ds->declarant->raison_sociale : $ds->declarant->nom ; ?>}
\def\DSClientAdresse{<?php echo ($ds->declarant->adresse)? "~" : $ds->declarant->adresse; ?>}
\def\DSClientCP{<?php echo $ds->declarant->code_postal; ?>}
\def\DSClientVille{<?php echo $ds->declarant->commune; ?>}

\def\InterloireAdresse{\textbf{INTERLOIRE} - 12, rue Etienne Pallu - BP 61921 - 37019 TOURS CEDEX 01 \\
		       Tél. : 02 47 60 55 17 - Fax : 02 47 60 55 19} 


\begin{document}

\begin{minipage}[t]{0.60\textwidth}
	\begin{tikzpicture}
		\node[inner sep=1pt] (tab0){
			\begin{tabular}{c}
  				\DSHEADTITRE \\
  				\DSHEADTEXTE \\
			\end{tabular}
		};
		\node[draw=gray, inner sep=0pt, rounded corners=3pt, line width=2pt, fit=(tab0.north west) (tab0.north east) (tab0.south east) (tab0.south west)] {};	
	\end{tikzpicture}
\end{minipage}
\hfill
\begin{minipage}[t]{0.40\textwidth}
\begin{flushright}
		\includegraphics[scale=0.8]{/var/www/vinsdeloire/project/web/data/logo.jpg}	
\end{flushright}
\end{minipage}
\bigskip

\begin{minipage}[t]{0.5\textwidth}
       \begin{flushleft}
		 \begin{tikzpicture}
            \node[inner sep=1pt] (tab2){
                    \begin{tabular}{>{\columncolor{lightgray}} l | p{72mm}}
				    
				    \centering Ref. Client &
                    \multicolumn{1}{r}{\DSClientNUM} \\

                    \centering \textbf{N° CVI} &
                    \multicolumn{1}{r}{\DSClientCVI} \\
                    
                    \centering Adresse cave : &
                    \multicolumn{1}{r}{. . . . . . . . . . . . . . . . . . .} \\
                    
                     &
                    \multicolumn{1}{r}{. . . . . . . . . . . . . . . . . . .} \\
                     &
                    \multicolumn{1}{r}{. . . . . . . . . . . . . . . . . . .} \\
                    
                    \centering Téléphone : &
                    \multicolumn{1}{r}{. . . . . . . . . . . . . . . . . . .} \\
                    
                    \centering Télécopie : &
                    \multicolumn{1}{r}{. . . . . . . . . . . . . . . . . . .} \\

					\centering Portable : &
                    \multicolumn{1}{r}{. . . . . . . . . . . . . . . . . . .} \\
                    \hline                 
                    \end{tabular}
            };
            \node[draw=gray, inner sep=0pt, rounded corners=3pt, line width=2pt, fit=(tab2.north west) (tab2.north east) (tab2.south east) (tab2.south west)] {};	
            \end{tikzpicture}        
       \end{flushleft}
\end{minipage}   
\hfill
\hspace{2cm}
\begin{minipage}[t]{0.5\textwidth}
\vspace{-2cm}
		\begin{flushleft}		
			\textbf{\DSClientNom \\}				
				\DSClientAdresse \\
				\DSClientCP ~\DSClientVille \\
			\end{flushleft}
		\hspace{6cm}
\end{minipage}

\begin{minipage}[t]{0.5\textwidth}
\begin{flushleft}
Référence : \textit{\textbf{\DSNUMERO}}
\end{flushleft}
\end{minipage}
\hfill
\begin{minipage}[t]{0.5\textwidth}
\begin{flushright}
page \thepage / 1
\end{flushright}
\end{minipage}
\begin{center}
\fbox{
\textbf{RENSEIGNEMENTS RELATIFS AUX STOCKS DE VIN AU \DSSTOCKSDATE}}
\end{center}

\centering
    \begin{tikzpicture}
		\node[inner sep=1pt] (tab1){

			\begin{tabular}{b{12mm}| m{118mm} |b{55mm}|}

  			\rowcolor{lightgray}
            \centering \textbf{Code} &
   			\centering \textbf{Produits} &
             \multicolumn{1}{>{\columncolor{lightgray}} c|}{ \textbf{Volume en hl}} 
   			 \\
  			\hline
                        <?php foreach ($ds->declarations as $declaration) :
                        ?>
                        <?php echo $declaration->code_douane; ?> &
                        <?php echo $declaration->produit_libelle; ?> &~\\ \hline
                        <?php
                        endforeach;
                        
                        for($i=0; $i<(34 - count($ds->declarations)); $i++) : ?>
                        ~ & ~ & ~ \\ \hline 
                        <?php endfor; ?>            
			\end{tabular}
		};
		\node[draw=gray, inner sep=0pt, rounded corners=3pt, line width=2pt, fit=(tab1.north west) (tab1.north east) (tab1.south east) (tab1.south west)] {};	

\end{tikzpicture}

\begin{center}
	\hspace{5cm}
Date et signature :	
\end{center}
\begin{center}
~\\~\\~\\
\InterloireAdresse
\end{center}

\end{document}
